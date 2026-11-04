// Browser regression tests. Only GETs the real product page; every review write is mocked.
const { test, before, after } = require('node:test');
const assert = require('node:assert/strict');
const path = require('node:path');
const os = require('node:os');
const { chromium } = require(process.env.REVIEW_PLAYWRIGHT_PATH || 'playwright');

const baseUrl = process.env.REVIEW_BASE_URL || 'http://localhost/course-work-PC-components-store';
const productUrl = `${baseUrl.replace(/\/$/, '')}/product/${process.env.REVIEW_PRODUCT_ID || 4}`;
let browser;

before(async () => {
    browser = await chromium.launch({ headless: true, ...(process.env.REVIEW_CHROME_PATH ?
        { executablePath: process.env.REVIEW_CHROME_PATH } : { channel: 'chrome' }) });
});
after(async () => { await browser?.close(); });

function controls() {
    return `<div class="review-card" data-review-id="101">
        <button type="button" class="reply-review-btn" data-review-id="101">Ответить</button>
        <button type="button" class="edit-review-btn" data-review-id="101" data-rating="4" data-comment="Основной отзыв">Редактировать отзыв</button>
        <div class="review-replies"><div class="review-card review-reply" data-review-id="102">
            <button type="button" class="edit-review-btn" data-review-id="102" data-is-reply="true" data-comment="Текст ответа">Редактировать ответ</button>
        </div></div>
    </div><button type="button" class="add-review-btn">Оставить отзыв</button>`;
}

async function withReviewPage(run) {
    const context = await browser.newContext();
    const page = await context.newPage();
    const requests = [];
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    // Never submit test comments to the actual store.
    await page.route('**/api/reviews/**', async route => {
        const request = route.request();
        const payload = new Request(request.url(), { method: request.method(), body: request.postDataBuffer(), headers: request.headers() });
        requests.push({ url: request.url(), fields: Object.fromEntries(await payload.formData()) });
        await route.fulfill({ contentType: 'application/json', body: '{"success":true,"message":"Сохранено"}' });
    });
    try {
        await page.goto(productUrl);
        await page.evaluate(html => {
            document.getElementById('reviews').innerHTML = html;
            switchTab('reviews');
            attachReplyListeners();
            attachEditListeners();
            if (typeof attachAddReviewListener === 'function') attachAddReviewListener();
        }, controls());
        // reloadReviews receives new markup, just like after a successful server save.
        await page.route(productUrl, route => route.fulfill({
            contentType: 'text/html; charset=utf-8',
            body: `<div id="reviews">${controls()}</div><button data-tab="reviews">Отзывы (1)</button>`
        }));
        await run(page, requests);
        assert.deepEqual(errors, []);
    } catch (error) {
        const screenshot = path.join(os.tmpdir(), `review-form-failure-${Date.now()}.png`);
        await page.screenshot({ path: screenshot }).catch(() => {});
        console.error(`Failure screenshot: ${screenshot}`);
        throw error;
    } finally {
        await context.close();
    }
}

async function submit(page) {
    const reloaded = page.waitForResponse(response => response.url() === productUrl);
    await page.locator('#reviewForm button[type="submit"]').click();
    await reloaded;
    await page.locator('#reviewModal').waitFor({ state: 'hidden' });
    await page.waitForFunction(() => !document.querySelector('#reviewForm button[type="submit"]').disabled);
}

test('reply submits a comment without a rating and works again after refresh', async () => {
    await withReviewPage(async (page, requests) => {
        for (let count = 1; count <= 2; count++) {
            await page.locator('.reply-review-btn').click();
            await page.locator('#reviewComment').fill(`Ответ ${count}`);
            assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
            assert.equal(await page.locator('#ratingGroup input:enabled').count(), 0);
            await submit(page);
            assert.equal(requests.length, count);
            assert.ok(requests[count - 1].url.endsWith('/api/reviews/add'));
            assert.equal(requests[count - 1].fields.parent_id, '101');
            assert.equal(requests[count - 1].fields.comment, `Ответ ${count}`);
            assert.equal(requests[count - 1].fields.rating, undefined);
        }
    });
});

test('editing an answer sends only its text and does not require hidden stars', async () => {
    await withReviewPage(async (page, requests) => {
        await page.locator('.edit-review-btn[data-is-reply="true"]').click();
        await page.locator('#reviewComment').fill('Изменённый ответ');
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
        assert.equal(await page.locator('#ratingGroup input:enabled').count(), 0);
        await submit(page);
        assert.equal(requests.length, 1);
        assert.ok(requests[0].url.endsWith('/api/reviews/update'));
        assert.equal(requests[0].fields.review_id, '102');
        assert.equal(requests[0].fields.rating, undefined);
    });
});

test('the new-review button is rebound after refresh and restores required ratings', async () => {
    await withReviewPage(async (page, requests) => {
        await page.locator('.reply-review-btn').click();
        await page.locator('#reviewComment').fill('Ответ для обновления списка');
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
        await submit(page);
        await page.locator('.add-review-btn').click();
        assert.equal(await page.locator('#reviewModal').isVisible(), true);
        assert.equal(await page.locator('#parentReviewId').inputValue(), '');
        assert.equal(await page.locator('#editReviewId').inputValue(), '');
        await page.locator('#reviewComment').fill('Новый отзыв');
        assert.equal(await page.locator('#ratingGroup').isVisible(), true);
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), false);
        await page.locator('label[for="star4"]').click();
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
        await submit(page);
        assert.equal(requests.length, 2);
        assert.equal(requests[1].fields.rating, '4');
        assert.equal(requests[1].fields.parent_id, '');
    });
});

test('switching between a review and a reply clears stale ratings and still requires a comment', async () => {
    await withReviewPage(async page => {
        await page.locator('.edit-review-btn:not([data-is-reply])').click();
        assert.equal(await page.locator('#star4').isChecked(), true);
        await page.getByRole('button', { name: 'Отмена', exact: true }).click();
        await page.locator('.reply-review-btn').click();
        assert.equal(await page.locator('#ratingGroup input:checked').count(), 0);
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), false);
        await page.locator('#reviewComment').fill('Комментарий обязателен');
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
        await page.getByRole('button', { name: 'Отмена', exact: true }).click();
        await page.locator('.edit-review-btn:not([data-is-reply])').click();
        assert.equal(await page.locator('#ratingGroup input:enabled').count(), 5);
        assert.equal(await page.locator('#star4').isChecked(), true);
        assert.equal(await page.locator('#reviewForm').evaluate(form => form.checkValidity()), true);
    });
});