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

function rootAttributes({ index = 0, pagingMode = 'index' } = {}) {
    return `class="mod-opening-hours" data-current-week-index="${index}" data-total-weeks="2" data-paging-mode="${pagingMode}" data-today-date-key=""`;
}

function visibleWeek(root) {
    return [...root.querySelectorAll('.mod-opening-hours__list')].find((list) => !list.hidden)?.dataset.weekLabel;
}

function flushObservers() {
    return new Promise((resolve) => setTimeout(resolve, 0));
}

function setEditorCanvas(enabled) {
    Object.defineProperty(window, 'frameElement', {
        configurable: true,
        get: () => (enabled ? { name: 'editor-canvas' } : null),
    });
}

describe('opening hours paging', () => {
    describe('block editor preview', () => {
        beforeAll(() => {
            setEditorCanvas(true);
            document.body.innerHTML = '';
            require('../source/js/opening-hours.js');
        });

        beforeEach(() => {
            document.body.innerHTML = '';
        });

        test('pages a block that is added after the canvas loads', async () => {
            document.body.insertAdjacentHTML(
                'beforeend',
                `<div ${rootAttributes()}>${weekMarkup(['Week 1', 'Week 2'])}</div>`,
            );
            await flushObservers();

            const root = document.querySelector('.mod-opening-hours');
            root.querySelector('.mod-opening-hours__paging-link--next').click();

            expect(visibleWeek(root)).toBe('Week 2');
        });

        test('rebinds paging after the preview reuses the root and replaces its markup', async () => {
            document.body.insertAdjacentHTML(
                'beforeend',
                `<div ${rootAttributes()}>${weekMarkup(['Week 1', 'Week 2'])}</div>`,
            );
            await flushObservers();

            const root = document.querySelector('.mod-opening-hours');
            expect(root.dataset.openingHoursInit).toBe('1');

            // ACF's JSX preview keeps this element, including data-opening-hours-init,
            // and swaps only the inner markup when a field changes.
            root.dataset.currentWeekIndex = '0';
            root.innerHTML = weekMarkup(['Updated 1', 'Updated 2', 'Updated 3']);
            expect(root.dataset.openingHoursInit).toBe('1');
            await flushObservers();

            const next = root.querySelector('.mod-opening-hours__paging-link--next');
            const prev = root.querySelector('.mod-opening-hours__paging-link--prev');
            next.click();

            expect(visibleWeek(root)).toBe('Updated 2');
            expect(root.querySelector('[data-week-title]').textContent).toBe('Updated 2');
            expect(root.querySelector('.mod-opening-hours__paging-info').textContent).toBe('2 / 3');
            expect(prev.disabled).toBe(false);
            expect(next.disabled).toBe(false);

            prev.click();
            expect(visibleWeek(root)).toBe('Updated 1');
        });

        test('binds paging when weeks first appear inside an empty block', async () => {
            document.body.insertAdjacentHTML('beforeend', `<div ${rootAttributes()}></div>`);
            await flushObservers();

            const root = document.querySelector('.mod-opening-hours');
            expect(root.dataset.openingHoursInit).toBeUndefined();

            root.innerHTML = weekMarkup(['Week 1', 'Week 2']);
            await flushObservers();

            root.querySelector('.mod-opening-hours__paging-link--next').click();
            expect(visibleWeek(root)).toBe('Week 2');
        });

        test('rebinds week-number paging after the inner markup is replaced', async () => {
            document.body.insertAdjacentHTML(
                'beforeend',
                `<div ${rootAttributes({ pagingMode: 'week-no' })}>${weekMarkup(['Week 1', 'Week 2', 'Week 3'])}</div>`,
            );
            await flushObservers();

            const root = document.querySelector('.mod-opening-hours');
            root.innerHTML = weekMarkup(['Week 1', 'Week 2', 'Week 3']);
            await flushObservers();

            root.querySelector('.mod-opening-hours__paging-link--next').click();

            expect(visibleWeek(root)).toBe('Week 2');
            expect(root.querySelector('.mod-opening-hours__paging-week--current').textContent).toBe('2');
        });
    });
});
