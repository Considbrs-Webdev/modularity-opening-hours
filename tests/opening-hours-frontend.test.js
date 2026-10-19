/**
 * @jest-environment jsdom
 */

function weekMarkup(labels) {
    const lists = labels
        .map(
            (label, index) => `
            <dl class="mod-opening-hours__list" data-week-index="${index}" data-week-label="${label}" data-week-no="${index + 1}"${index === 0 ? '' : ' hidden="hidden"'}>
                <div class="mod-opening-hours__row" data-date-key="2026-10-1${index}">
                    <dt class="mod-opening-hours__day">${label}</dt>
                    <dd class="mod-opening-hours__slots">9–17</dd>
                </div>
            </dl>`,
        )
        .join('');

    return `
        <div class="mod-opening-hours__week-header">
            <h3 class="mod-opening-hours__week-title" data-week-title>${labels[0]}</h3>
            <nav class="mod-opening-hours__paging">
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--prev" disabled>Previous week</button>
                <span class="mod-opening-hours__paging-info">1 / ${labels.length}</span>
                <button type="button" class="mod-opening-hours__paging-link mod-opening-hours__paging-link--next">Next week</button>
            </nav>
        </div>
        <div class="mod-opening-hours__list-container">${lists}</div>
    `;
}

document.body.innerHTML = `<div class="mod-opening-hours" data-current-week-index="0" data-paging-mode="index">${weekMarkup(['Week 1', 'Week 2', 'Week 3'])}</div>`;
require('../source/js/opening-hours.js');

test('front-end paging advances one week per click', () => {
    const root = document.querySelector('.mod-opening-hours');
    const next = root.querySelector('.mod-opening-hours__paging-link--next');

    next.click();
    expect([...root.querySelectorAll('.mod-opening-hours__list')].find((list) => !list.hidden)?.dataset.weekLabel).toBe(
        'Week 2',
    );

    next.click();
    expect([...root.querySelectorAll('.mod-opening-hours__list')].find((list) => !list.hidden)?.dataset.weekLabel).toBe(
        'Week 3',
    );
    expect(next.disabled).toBe(true);
});
