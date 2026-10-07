const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

(async () => {
    const tabs = await (await fetch('http://127.0.0.1:9225/json/list')).json();
    const socket = new WebSocket(tabs[0].webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
    let id = 0;
    let errors = 0;
    const pending = new Map();
    const events = new Map();
    socket.onmessage = event => {
        const message = JSON.parse(event.data);
        if (message.method === 'Runtime.exceptionThrown') errors++;
        if (message.id) {
            const job = pending.get(message.id);
            if (job) { pending.delete(message.id); message.error ? job.reject(new Error(message.error.message)) : job.resolve(message.result); }
        } else events.get(message.method)?.(message.params);
    };
    const call = (method, params = {}) => new Promise((resolve, reject) => {
        pending.set(++id, { resolve, reject });
        socket.send(JSON.stringify({ id, method, params }));
    });
    await call('Page.enable');
    await call('Runtime.enable');
    // Snapshot interactions never make requests or submit edits to the application.
    await call('Page.addScriptToEvaluateOnNewDocument', { source: 'window.fetch = async () => new Response("{}", {status: 401});' });
    const loaded = new Promise(resolve => events.set('Page.loadEventFired', resolve));
    await call('Page.navigate', { url: pathToFileURL(path.resolve('storage/app/private/table-profile.html')).href });
    await loaded;
    const evaluate = async expression => {
        const result = await call('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
        if (result.exceptionDetails) throw new Error('Browser interaction failed: ' + result.exceptionDetails.text);
        return result.result.value;
    };
    console.log(JSON.stringify(await evaluate('({rows:document.querySelectorAll("#performanceTable tbody tr[data-row-id]").length, initialInputs:document.querySelectorAll(".month-box").length})')));
    for (const button of ['targetBtn', 'accompBtn', 'financialBtn', 'financialAccompBtn', 'monthBtn']) {
        const metrics = await evaluate(`(async () => {
            const button = document.getElementById(${JSON.stringify(button)});
            if (!button) return {button:${JSON.stringify(button)}, missing:true};
            const started = performance.now();
            button.click();
            const handlerMs = performance.now()-started;
            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
            return {button:button.id,handlerMs:Math.round(handlerMs),settledMs:Math.round(performance.now()-started),inputs:document.querySelectorAll('.month-box').length};
        })()`);
        console.log(JSON.stringify(metrics));
    }
    console.log(JSON.stringify(await evaluate(`(async () => {
        const rows = [...document.querySelectorAll('#performanceTable tbody tr[data-row-id]')];
        const core = rows[0].dataset.coreKey;
        const started = performance.now();
        window.toggleRowsByCoreKey(core);
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const groupRows = rows.filter(row => row.dataset.coreKey === core);
        const inputs = groupRows.flatMap(row => [...row.querySelectorAll('.month-box')]);
        const savedTargetsMatch = groupRows.every(row => [...row.querySelectorAll('.month-box[data-section="target"]')]
            .filter(input => input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1' && PERIODS[Number(input.dataset.col)]?.type === 'month')
            .every(input => Number(input.value) === Number(existingTargetsByIndicator[row.dataset.rowId]?.[row.dataset.indicatorId]?.[input.dataset.officeId]?.[PERIOD_KEYS[Number(input.dataset.col)]] || 0)));
        const expansionMs = Math.round(performance.now()-started);
        const editable = inputs.find(input => input.dataset.section === 'financial' && !input.readOnly && input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1');
        if (editable) { editable.dataset.profileEdited = '1'; editable.value = '123.45'; editable.dispatchEvent(new Event('input', {bubbles:true})); }
        window.toggleRowsByCoreKey(core);
        const otherCore = rows.find(row => row.dataset.coreKey !== core)?.dataset.coreKey;
        if (otherCore) window.toggleRowsByCoreKey(otherCore);
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        window.toggleRowsByCoreKey(core);
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        const result = {expandedRows:groupRows.length,expandedInputs:inputs.length,expansionMs,savedTargetsMatch,unsavedFinancialEditPreserved:!editable||editable.value==='123.45',noDuplicateInputs:groupRows.reduce((sum,row)=>sum+row.querySelectorAll('.month-box').length,0)===inputs.length};
        if (!savedTargetsMatch || !result.unsavedFinancialEditPreserved || !result.noDuplicateInputs || inputs.length===0) throw new Error('Lazy row data verification failed.');
        return result;
    })()`)));
    console.log(JSON.stringify(await evaluate(`(async () => {
        const row = [...document.querySelectorAll('#performanceTable tbody tr[data-row-id]')]
            .find(row => row.style.display !== 'none' && !row.querySelector('.month-box'));
        if (!row) throw new Error('Missing deferred expanded row for scroll verification.');
        const started = performance.now();
        row.scrollIntoView({block:'center',behavior:'instant'});
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => requestAnimationFrame(resolve))));
        const inputs = [...row.querySelectorAll('.month-box[data-section="target"]')];
        const savedValuesMatch = inputs.filter(input => input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1' && PERIODS[Number(input.dataset.col)]?.type === 'month')
            .every(input => Number(input.value) === Number(existingTargetsByIndicator[row.dataset.rowId]?.[row.dataset.indicatorId]?.[input.dataset.officeId]?.[PERIOD_KEYS[Number(input.dataset.col)]] || 0));
        const edited = document.querySelector('[data-profile-edited="1"]');
        const result = {scrollLoadMs:Math.round(performance.now()-started),deferredRowLoaded:inputs.length>0,savedValuesMatch,unsavedEditPreserved:!edited||edited.value==='123.45'};
        if (!result.deferredRowLoaded || !savedValuesMatch || !result.unsavedEditPreserved) throw new Error('Scroll data verification failed.');
        return result;
    })()`)));
    console.log(JSON.stringify({ browserErrors: errors }));
    socket.close();
})().catch(error => { console.error(error.message); process.exitCode = 1; });
