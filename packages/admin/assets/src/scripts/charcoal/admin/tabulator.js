/**
 * Charcoal Tabulator Handler
 */

var Tabulator = Tabulator || {};

;(function (Charcoal, $, document, Tabulator) {
    'use strict';

    /**
     * Tabulator Input Property
     *
     * charcoal/admin/property/input/tabulator
     *
     * Require:
     * - tabulator
     *
     * @param {Object} opts - Options for input property.
     */
    Charcoal.Admin.Property_Input_Tabulator = function (opts) {
        this.input_type = 'charcoal/admin/property/input/tabulator';

        Charcoal.Admin.Property.call(this, opts);

        this.input_id = null;
        this.tabulator_selector = null;
        this.tabulator_element = null;
        this.tabulator_options = null;
        this.tabulator_instance = null;

        this.set_properties(opts)
            .create_tabulator()
            .set_events()
    };
    Charcoal.Admin.Property_Input_Tabulator.prototype = Object.create(Charcoal.Admin.Property.prototype);
    Charcoal.Admin.Property_Input_Tabulator.prototype.constructor = Charcoal.Admin.Property_Input_Tabulator;
    Charcoal.Admin.Property_Input_Tabulator.prototype.parent = Charcoal.Admin.Property.prototype;

    // Set the tabulator properties.
    Charcoal.Admin.Property_Input_Tabulator.prototype.set_properties = function (opts) {
        this.input_id = opts.id || this.input_id;
        this.tabulator_selector   = opts.data.tabulator_selector || this.tabulator_selector;
        this.tabulator_element    = opts.data.tabulator_element || this.tabulator_element;
        this.tabulator_options    = opts.data.tabulator_options || this.tabulator_options;
        this.tabulator_properties = opts.data.tabulator_properties || this.tabulator_properties;

        if (!this.tabulator_element && this.tabulator_selector) {
            this.tabulator_element = document.querySelector(this.tabulator_selector);
        }

        if (!this.tabulator_element) {
            return;
        }

        var that = this;

        // Setup the columns
        var columns = this.tabulator_properties.map(function (column) {
            // Remove the settings property because it is not recognised by Tabulator
            var cleanColumn = {}
            for (var key in column) {
                if (key !== 'settings') {
                    cleanColumn[key] = column[key]
                }
            }
            return cleanColumn
        });

        if (this.tabulator_options.movableRows) {
            columns.unshift(
                {
                    formatter: 'handle',
                    headerSort: false,
                    frozen: true,
                    width: 30,
                    minWidth: 30,
                    rowHandle: true,
                    resizable: false
                }
            )
        }

        // Add row Button
        columns.push({
            formatter: function () {
                return '<i class=\'fa fa-plus text-primary\'></i>';
            },
            hozAlign: 'center',
            vertAlign: 'center',
            headerSort: false,
            frozen: true,
            width: 40,
            minWidth: 30,
            resizable: false,
            cellClick: function (e, cell) {
                that.add_row(cell.getRow());
                that.update_data();
            }
        });

        // Remove Row Button
        columns.push({
            formatter: function () {
                return '<i class=\'fa fa-minus text-danger\'></i>';
            },
            hozAlign: 'center',
            vertAlign: 'center',
            headerSort: false,
            frozen: true,
            width: 40,
            minWidth: 30,
            resizable: false,
            cellClick: function (e, cell) {
                that.tabulator_instance.deleteRow(cell.getRow());
                that.update_data();
            }
        })

        var default_opts = {
            tabEndNewRow: this.new_row_data(),
            columns: columns,
        };

        this.tabulator_options = $.extend({}, default_opts, this.tabulator_options, {data: that.get_data()});

        return this;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.set_events = function () {
        var that = this;

        // Handle add row event.
        $('.js-' + this.input_id + '-add').on('click', function () {
            that.add_row();
            that.update_data();
        });

        // After the table is built, redraw it instantly to hide the columns that are related to a specific language.
        this.tabulator_instance.on('tableBuilt', function () {
            that.switch_language();
        })

        this.tabulator_instance.on('dataChanged', function (){
            that.update_data()
        });
        
        // Update the columns to display when the language switcher is toggled.
        $(document).on('switch_language.charcoal', function () {
            that.switch_language();
        });

        return this
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.switch_language = function () {
        var that = this
        var currentLanguage = Charcoal.Admin.lang();
        var columns = that.tabulator_properties;

        columns.forEach(function (column) {
            var fieldName = column.field;

            if (column.settings !== undefined && column.settings.language) {
                if (column.settings.language === currentLanguage) {
                    that.tabulator_instance.showColumn(fieldName);
                } else {
                    that.tabulator_instance.hideColumn(fieldName);
                }
            } else {
                that.tabulator_instance.showColumn(fieldName);
            }
        })

        this.tabulator_instance.redraw();

        return this;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.new_row_data = function () {
        return {
            'active': true,
        }
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.add_row = function (index) {
        this.tabulator_instance.addRow(this.new_row_data(), false, index);
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.update_data = function () {
        var data = JSON.stringify(this.tabulator_instance.getData());
        $(this.tabulator_selector).val(data);
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.get_data = function () {
        return $(this.tabulator_selector).val() || '[' + JSON.stringify(this.new_row_data()) + ']';
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.create_tabulator = function () {
        this.tabulator_instance = new Tabulator(this.tabulator_selector + '-tabulator', this.tabulator_options);
        return this;
    };

}(Charcoal, jQuery, document, Tabulator));
