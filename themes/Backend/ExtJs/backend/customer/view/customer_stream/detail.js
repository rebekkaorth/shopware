/* global Ext */
// {namespace name=backend/customer_stream/translation}

Ext.define('Shopware.apps.Customer.view.customer_stream.Detail', {
    extend: 'Shopware.model.Container',

    configure: function () {
        var factory = Ext.create('Shopware.attribute.SelectionFactory');

        return {
            splitFields: false,
            fieldSets: [{
                title: '{s name=detail/stream_details}Stream details{/s}',
                fields: {
                    name: '{s name=detail/stream_name}Name:{/s}',
                    description: '{s name=detail/stream_description}Description:{/s}',
                    productStreamIds: {
                        xtype: 'shopware-form-field-product-stream-grid',
                        height: 300,
                        store: factory.createEntitySearchStore('Shopware\\Models\\ProductStream\\ProductStream'),
                        searchStore: factory.createEntitySearchStore('Shopware\\Models\\ProductStream\\ProductStream'),
                        fieldLabel: '{s name=detail/product_stream}Product stream{/s}'
                    }
                }
            }]
        };
    }
});