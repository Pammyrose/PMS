const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

(async () => {
    const tabs = await (await fetch('http://127.0.0.1:9225/json/list')).json();
    const socket = new WebSocket(tabs[0].webSocketDebuggerUrl);
    await new Promise((resolve, reject) => { socket.onopen = resolve; socket.onerror = reject; });
    let id = 0, errors = 0;
    const pending = new Map(), events = new Map();
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
    const evaluate = async expression => {
        const result = await call('Runtime.evaluate', { expression, awaitPromise: true, returnByValue: true });
        if (result.exceptionDetails) throw new Error('Dashboard interaction failed: ' + result.exceptionDetails.text);
        return result.result.value;
    };
    try {
        await call('Page.enable'); await call('Runtime.enable');
        await call('Page.addScriptToEvaluateOnNewDocument', { source: 'window.fetch = async () => new Response("{}", {status:401});' });
        const loaded = new Promise(resolve => events.set('Page.loadEventFired', resolve));
        await call('Page.navigate', { url: pathToFileURL(path.resolve('storage/app/private/dashboard-full-profile.html')).href });
        await loaded;
        const fixtures = Object.fromEntries(['partial', 'pap', 'indicator'].map(name => [name, fs.readFileSync(`storage/app/private/dashboard-${name}-profile.html`, 'utf8')]));
        await evaluate(`window.profileFixtures = ${JSON.stringify(fixtures)}; window.profileRequests=[];
            window.fetch=async(url,options)=> { const type=options.headers['X-Dashboard-List']||'partial'; window.profileRequests.push(type); return new Response(window.profileFixtures[type]); };`);
        console.log(JSON.stringify(await evaluate(`({initialElements:document.querySelectorAll('*').length,initialDetailRows:document.querySelectorAll('.dashboard-link-row').length,domReadyMs:Math.round(performance.getEntriesByType('navigation')[0].domContentLoadedEventEnd)})`)));
        const filter = await evaluate(`(async()=>{
            const started=performance.now();
            document.getElementById('dashboardFilterForm').action=window.location.href;
            const select=document.getElementById('dashboard_sector');select.value='gass';select.dispatchEvent(new Event('change',{bubbles:true}));
            while(document.querySelector('main').hasAttribute('aria-busy')) await new Promise(resolve=>setTimeout(resolve,20));
            await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));
            if(document.getElementById('dashboard_sector').value!=='gass') throw new Error('Filter results missing');
            return {filterMs:Math.round(performance.now()-started),detailRequests:window.profileRequests.filter(type=>type!=='partial').length};
        })()`);
        console.log(JSON.stringify(filter));
        if (filter.detailRequests) throw new Error('Hidden lists loaded during filtering');
        for (const type of ['pap', 'indicator']) {
            const result = await evaluate(`(async()=>{
                const modal=document.getElementById('${type}ListModal');
                const body=modal.querySelector('[data-dashboard-list]');
                const started=performance.now();
                document.querySelector('[data-bs-target="#${type}ListModal"]').click();
                while(body.dataset.loaded!=='true') await new Promise(resolve=>setTimeout(resolve,20));
                const button=body.querySelector('[data-${type}-sector-toggle]');
                if(!button) throw new Error('List missing sector groups');
                button.click();
                await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));
                const visible=body.querySelectorAll('.dashboard-link-row:not(.d-none)').length;
                if(!visible) throw new Error('Sector rows did not expand');
                button.click();
                if(body.querySelectorAll('.dashboard-link-row:not(.d-none)').length) throw new Error('Sector rows did not collapse');
                const elapsed=Math.round(performance.now()-started);
                const instance=bootstrap.Modal.getInstance(modal);
                await new Promise(resolve=>setTimeout(resolve,400));instance.hide();
                await new Promise(resolve=>setTimeout(resolve,400));
                document.querySelector('[data-bs-target="#${type}ListModal"]').click();
                await new Promise(resolve=>setTimeout(resolve,400));instance.hide();
                await new Promise(resolve=>setTimeout(resolve,400));
                return {list:'${type}',openAndExpandMs:elapsed,visibleRows:visible,requests:window.profileRequests.filter(type=>type==='${type}').length};
            })()`);
            console.log(JSON.stringify(result));
            if (result.requests !== 1) throw new Error('Reopening fetched duplicate records');
        }
        console.log(JSON.stringify({ browserErrors: errors }));
        if (errors) throw new Error('Browser runtime errors detected');
    } finally {
        await call('Browser.close').catch(() => {});
        socket.close();
    }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
