const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { resolve } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

for (const resource of ['models', 'consumables']) {
test(`${resource} modal save uses only the creation form when purchase modals exist`, () => {
    let save;
    let request;
    const modelData = 'name=ever&category_id=1&_token=test-token' + (resource === 'consumables' ? '&qty=0&location_id=2' : '');
    const creationForm = {
        attr: () => '/api/v1/' + resource,
        serialize: () => modelData,
    };
    const purchaseForms = {
        attr: () => undefined,
        serialize: () => 'purchase_cost=100&nds=20&' + modelData,
    };
    const modal = {
        length: 1,
        on: (event, selector, handler) => {
            if (event === 'click' && selector === '#modal-save') {
                save = handler;
            }
        },
    };
    const $ = (selector) => {
        if (typeof selector === 'function') {
            selector();
            return;
        }
        if (selector === '#createModal') return modal;
        if (selector === '#createModal .modal-body form') return creationForm;
        if (selector === '.modal-body form') return purchaseForms;
        if (selector === 'meta[name="baseUrl"]') return { attr: () => '/' };
        if (selector === 'meta[name="csrf-token"]') return { attr: () => 'test-token' };
        throw new Error('Unexpected selector: ' + selector);
    };
    $.ajax = (options) => { request = options; };

    vm.runInNewContext(readFileSync(resolve(__dirname, '../../resources/assets/js/snipeit_modals.js'), 'utf8'), { $ });
    save();

    assert.equal(request.type, 'POST');
    assert.equal(request.url, '/api/v1/' + resource);
    assert.equal(request.data, modelData);
    assert.equal(request.headers['X-CSRF-TOKEN'], 'test-token');
});
}
