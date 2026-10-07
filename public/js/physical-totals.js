// Non-cumulative quarters use the most frequent month; annual totals use the most frequent quarter.
window.pmsMostFrequentTotal = function (values) {
    const counts = new Map();
    let result = 0;
    let highestCount = 0;
    for (const value of values) {
        const count = (counts.get(value) || 0) + 1;
        counts.set(value, count);
        if (count > highestCount || (count === highestCount && value > result)) {
            result = value;
            highestCount = count;
        }
    }
    return result;
};
