(() => {
    const previousFilters = new WeakMap();
    window.pmsPopulatePapOptions = (datalist, source, itemField, parentValues, matchesParents) => {
        const key = JSON.stringify([itemField, parentValues]);
        const previous = previousFilters.get(datalist);
        if (previous?.key === key && previous.source === source) return;

        const seen = new Set();
        const fragment = document.createDocumentFragment();
        source.forEach(item => {
            if (!matchesParents(item)) return;
            const value = String(item?.[itemField] || '').trim();
            const normalized = value.replace(/\s+/g, ' ').toLowerCase();
            if (!normalized || seen.has(normalized)) return;
            seen.add(normalized);
            const option = document.createElement('option');
            option.value = value;
            fragment.appendChild(option);
        });
        datalist.replaceChildren(fragment);
        previousFilters.set(datalist, { key, source });
    };
})();
