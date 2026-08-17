(() => {
    const results = document.getElementById('notificationResults');
    if (!results) return;

    let refreshInFlight = false;
    let refreshQueued = false;

    const isEditing = () => Boolean(
        results.querySelector('input:focus, textarea:focus, select:focus')
    );

    const syncBadge = (incomingDocument) => {
        const currentBadge = document.getElementById('notificationCountBadge');
        const incomingBadge = incomingDocument.getElementById('notificationCountBadge');
        if (!currentBadge || !incomingBadge) return;

        currentBadge.textContent = incomingBadge.textContent;
        currentBadge.className = incomingBadge.className;
        currentBadge.hidden = incomingBadge.hidden;
        currentBadge.dataset.version = incomingBadge.dataset.version || '';
        currentBadge.setAttribute(
            'aria-label',
            incomingBadge.getAttribute('aria-label') || 'notifications'
        );
    };

    async function refreshContent() {
        if (refreshInFlight) {
            refreshQueued = true;
            return;
        }

        if (isEditing()) {
            refreshQueued = true;
            return;
        }

        refreshInFlight = true;

        try {
            const response = await fetch(window.location.href, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) throw new Error(`Notification content request failed (${response.status})`);

            const html = await response.text();
            const incomingDocument = new DOMParser().parseFromString(html, 'text/html');
            const incomingResults = incomingDocument.getElementById('notificationResults');
            if (!incomingResults) throw new Error('Notification content was missing from the response.');

            results.innerHTML = incomingResults.innerHTML;
            syncBadge(incomingDocument);
        } catch (error) {
            console.warn('Notification content could not be refreshed.', error);
        } finally {
            refreshInFlight = false;

            if (refreshQueued && !isEditing()) {
                refreshQueued = false;
                window.setTimeout(refreshContent, 250);
            }
        }
    }

    window.addEventListener('pms:notifications-updated', refreshContent);
    results.addEventListener('focusout', () => {
        if (refreshQueued) window.setTimeout(refreshContent, 250);
    });
})();
