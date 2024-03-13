/* global formWidgetL10n commonL10n tabulatorWidgetL10n Tabulator */

(function (Charcoal, $, document, Tabulator) {

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
        Charcoal.Admin.Property.call(this, opts);

        this.input_type = 'charcoal/admin/property/input/tabulator';

        this.input_id = null;

        this.property_label     = null;
        this.tabulator_selector = null;
        this.tabulator_input    = null;
        this.tabulator_options  = null;
        this.tabulator_instance = null;
        this.tabulator_events   = [];

        this.set_properties(opts).create_tabulator();
        this.set_events();
    };
    Charcoal.Admin.Property_Input_Tabulator.prototype = Object.create(Charcoal.Admin.Property.prototype);
    Charcoal.Admin.Property_Input_Tabulator.prototype.constructor = Charcoal.Admin.Property_Input_Tabulator;
    Charcoal.Admin.Property_Input_Tabulator.prototype.parent = Charcoal.Admin.Property.prototype;

    Charcoal.Admin.Property_Input_Tabulator.prototype.set_properties = function (opts) {
        this.input_id = opts.id || this.input_id;

        if (typeof opts.data === 'function') {
            opts.data = opts.data.apply(this);
        }

        this.input_options = opts.data.input_options || this.input_options;

        this.tabulator_selector = opts.data.tabulator_selector || this.tabulator_selector;
        this.tabulator_input    = opts.data.tabulator_input    || this.tabulator_input;
        this.tabulator_options  = opts.data.tabulator_options  || this.tabulator_options;
        this.tabulator_events   = opts.data.tabulator_events   || this.tabulator_events;

        if (this.tabulator_events && !Array.isArray(this.tabulator_events)) {
            const tabulator_events_map = this.tabulator_events;
            this.tabulator_events = [];

            for (const event in tabulator_events_map) {
                const listeners = Array.isArray(tabulator_events_map[event])
                    ? tabulator_events_map[event]
                    : [ tabulator_events_map[event] ];

                for (const listener of listeners) {
                    if (typeof listener === 'function') {
                        this.tabulator_events.push([ event, listener ]);
                    } else {
                        console.warn('[Charcoal.Property.Tabulator]', 'Bad event listener:', event, listener);
                    }
                }
            }
        }

        if (this.tabulator_selector) {
            if (!this.tabulator_input) {
                this.tabulator_input = document.querySelector(this.tabulator_selector);
            }
        }

        if (!this.tabulator_input) {
            console.error('Tabulator input element or selector not defined');
            return;
        }

        if (!this.property_label && this.tabulator_input.id) {
            var tabulator_input_id = this.tabulator_input.id.replace(/_[a-z]{2}$/, '');
            var property_label_selector = '[for="' + tabulator_input_id + '"]';
            this.property_label = (
                document.querySelector(property_label_selector).textContent ??
                this.tabulator_input.name
            );
        }

        var default_tabulator_options = {
            rowFormatter: (row) => {
                var data = row.getData();

                if (typeof data.active !== 'undefined') {
                    const rowElem = row.getElement();

                    if (data.active !== true) {
                        rowElem.style.backgroundColor = '#FFEFEF';
                    } else {
                        rowElem.style.backgroundColor = null;
                    }
                }
            },
            footerElement: `${this.tabulator_selector}-tabulator-footer`,
        };

        if (typeof opts.pre_tabulator_options === 'function') {
            opts.pre_tabulator_options.apply(this);
        }

        this.tabulator_options = Object.assign({}, default_tabulator_options, this.tabulator_options);

        if (typeof opts.post_tabulator_options === 'function') {
            opts.post_tabulator_options.apply(this);
        }

        this.parse_tabulator_options();

        return this;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_options = function () {
        /**
         * Adds support to reuse custom 'newRowData' definition
         * for Tabulator's new row on tab option.
         */
        if (this.tabulator_options?.tabEndNewRow === 'newRowData') {
            this.tabulator_options.tabEndNewRow = (this.input_options.newRowData || true);
        }

        /**
         * Adds support for merging predefined columns and
         * automatic column generation, and automatic column templating.
         */
        if (this.tabulator_options?.autoColumns) {
            if (
                !this.tabulator_options?.autoColumnsDefinitions &&
                (
                    this.tabulator_options?.columns ||
                    this.input_options?.autoColumnTemplates
                )
            ) {
                this.tabulator_options.autoColumnsDefinitions = (definitions) => {
                    if (Array.isArray(this.tabulator_options?.columns)) {
                        /**
                         * Replace any automatic column definition with
                         * a predefined column equivalent.
                         */
                        definitions = definitions.filter(
                            ({ field: autoField }) => !this.tabulator_options.columns.some(
                                ({ field: colField }) => autoField === colField
                            )
                        );

                        /**
                         * Merge predefined columns with automatic column definitions.
                         */
                        definitions = this.tabulator_options.columns.concat(definitions);
                    }

                    const distinctTemplates = this.input_options?.autoColumnTemplates;

                    if (!distinctTemplates) {
                        return definitions;
                    }

                    const calcAutoColumnStartIndex = (typeof this.input_options?.autoColumnStartIndex === 'number');

                    return definitions.map((column) => {
                        const field = column.field;

                        if (typeof field === 'undefined') {
                            return column;
                        }

                        if (distinctTemplates) {
                            for (const key in distinctTemplates) {
                                let query;

                                try {
                                    query = parseAutoColumnTemplatesKeyQuery(key);
                                } catch (err) {
                                    console.error('[Charcoal.Property.Tabulator]', 'Bad autoColumnTemplates key:', key, err);
                                    break;
                                }

                                const template = distinctTemplates[key];

                                if (query instanceof RegExp) {
                                    if (query.test(field)) {
                                        column = Object.assign({}, column, template);

                                        column.title = field.replace(query, template.title);
                                        column.field = field.replace(query, template.field);

                                        if (calcAutoColumnStartIndex && column.autoColumnStartIndex) {
                                            this.input_options.autoColumnStartIndex = parseInt(field.replace(query, column.autoColumnStartIndex));
                                            // console.log('- Initial Column:', this.input_options.autoColumnStartIndex);
                                        }

                                        // Custom option is deleted to avoid invalid column definition warning.
                                        delete column.autoColumnStartIndex;
                                    }
                                } else {
                                    if (query === field) {
                                        column = Object.assign({}, column, template);

                                        column.title = template.title;
                                        column.field = template.field;
                                    }
                                }
                            }
                        }

                        column = this.parse_tabulator_column_definition(column);

                        return column;
                    });
                };
            }
        } else if (this.tabulator_options?.columns) {
            this.tabulator_options.columns = this.tabulator_options.columns.map(
                (column) => this.parse_tabulator_column_definition(column)
            );
        }

        if (Array.isArray(this.tabulator_options?.groupContextMenu)) {
            this.tabulator_options.groupContextMenu = this.tabulator_options.groupContextMenu.map((item) => this.parse_tabulator_column_menu_item(item));
        }

        if (Array.isArray(this.tabulator_options?.rowContextMenu)) {
            this.tabulator_options.rowContextMenu = this.tabulator_options.rowContextMenu.map((item) => this.parse_tabulator_row_menu_item(item));
        }
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_column_definition = function (column) {
        if (Array.isArray(column?.clickMenu)) {
            column.clickMenu = column.clickMenu.map((item) => this.parse_tabulator_cell_menu_item(item));
        }

        if (Array.isArray(column?.contextMenu)) {
            column.contextMenu = column.contextMenu.map((item) => this.parse_tabulator_cell_menu_item(item));
        }

        if (Array.isArray(column?.headerContextMenu)) {
            column.headerContextMenu = column.headerContextMenu.map((item) => this.parse_tabulator_column_menu_item(item));
        }

        if (Array.isArray(column?.headerMenu)) {
            column.headerMenu = column.headerMenu.map((item) => this.parse_tabulator_column_menu_item(item));
        }

        if (typeof column?.cellClick === 'string') {
            column.cellClick = this.parse_tabulator_cell_click_action(column.cellClick);
        }

        if (typeof column?.formatterIcon === 'string') {
            const formatterIcon = column.formatterIcon;
            delete column.formatterIcon;

            column.formatter = () => {
                return `<i class="fa fa-${formatterIcon}"></i>`;
            };
        }

        if (
            column?.validator === 'required' ||
            (Array.isArray(column?.validator) && column.validator.includes('required'))
        ) {
            column.title += ' <span class="text-danger">*</span>';
        }

        return column;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_cell_click_action = function (action) {
        switch (action) {
            case 'addRow':
                return (_event, cell) => {
                    this.add_row(cell.getRow());
                    this.update_input_data();
                };

            case 'removeRow':
                return (_event, cell) => {
                    this.tabulator_instance.deleteRow(cell.getRow());
                    this.update_input_data();
                };

            case 'toggleValue':
                return (_event, cell) => {
                    cell.setValue(!cell.getValue());
                };
        }

        return null;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_cell_menu_item = function (item) {
        if (typeof item?.action === 'string') {
            if (typeof cellMenuItemActions[item.action] === 'function') {
                item.action = cellMenuItemActions[item.action].bind(this);
            }
        }

        return item;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_column_menu_item = function (item) {
        if (typeof item?.action === 'string') {
            if (typeof columnMenuItemActions[item.action] === 'function') {
                item.action = columnMenuItemActions[item.action].bind(this);
            }
        }

        return item;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.parse_tabulator_row_menu_item = function (item) {
        if (typeof item?.action === 'string') {
            if (typeof rowMenuItemActions[item.action] === 'function') {
                item.action = rowMenuItemActions[item.action].bind(this);
            }
        }

        return item;
    };

    Charcoal.Admin.Property_Input_Tabulator.prototype.set_events = function () {
        $(`.js-${this.input_id}-add-col`).on('click.charcoal.tabulator', () => {
            this.add_col();
            this.update_input_data();
        });

        $(`.js-${this.input_id}-add-row`).on('click.charcoal.tabulator', () => {
            this.add_row();
            this.update_input_data();
        });

        $(`.js-${this.input_id}-history-undo`).on('click.charcoal.tabulator', () => {
            this.tabulator_instance.undo();
            this.update_input_data();
        });

        $(`.js-${this.input_id}-history-redo`).on('click.charcoal.tabulator', () => {
            this.tabulator_instance.redo();
            this.update_input_data();
        });
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.new_col_data = function () {
        let column = this.input_options.newColumnData;

        if (typeof column === 'string') {
            // let query;

            // try {
            //     query = parseAutoColumnTemplatesKeyQuery(column);
            // } catch (err) {
            //     console.error('[Charcoal.Property.Tabulator]', 'Bad autoColumnTemplates key:', column, err);
            //     return {};
            // }

            if (this.input_options?.autoColumnTemplates[column]) {
                const template = this.input_options.autoColumnTemplates[column];

                column = Object.assign({}, template);

                if (typeof this.input_options?.autoColumnStartIndex === 'number') {
                    const index = ++(this.input_options.autoColumnStartIndex);

                    // console.log('+ New Column:', index);

                    column.title = column.title.replace('$1', index);
                    column.field = column.field.replace('$1', index);

                    // Custom option is deleted to avoid invalid column definition warning.
                    delete column.autoColumnStartIndex;
                }
            }
        } else {
            column = Object.assign({}, column);
        }

        column = this.parse_tabulator_column_definition(column);

        if (Array.isArray(column)) {
            return [ ...column ];
        }

        return Object.assign({}, column);
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.new_row_data = function () {
        let row = this.input_options?.newRowData || {};

        if (Array.isArray(row)) {
            return [ ...row ];
        }

        return Object.assign({}, row);
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.add_col = function (index = null) {
        const addColumn = this.tabulator_instance.addColumn(this.new_col_data(), (void 0), index);

        if (this.input_options?.columnsManipulateData) {
            addColumn.then((column) => {
                const field = column.getField();

                this.tabulator_instance.getRows().forEach((row) => {
                    const data = row.getData();

                    if (typeof data[field] === 'undefined') {
                        data[field] = this.input_options?.newRowData?.[field] ?? '';

                        row.update(data);
                    }
                });
            });
        }
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.add_row = function (index = null) {
        this.tabulator_instance.setHeight()
        this.tabulator_instance.addRow(this.new_row_data(), (void 0), index);
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.delete_column = function (column) {
        if (typeof column === 'string') {
            column = this.tabulator_instance.getColumn(column);

            if (!column) {
                console.error('[Charcoal.Property.Tabulator]', 'Column does not exist:', column);
                return;
            }
        }

        const field = column.getField();

        const deleteColumn = column.delete();

        if (this.input_options?.columnsManipulateData) {
            deleteColumn.then(() => {
                this.tabulator_instance.getRows().forEach((row) => {
                    const data = row.getData();

                    if (typeof data[field] !== 'undefined') {
                        delete data[field];

                        row.update(data);
                    }
                });
            });
        }
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.update_input_data = function () {
        try {
            const data = this.get_table_data();
            const json = JSON.stringify(data);

            this.tabulator_input.value = json;
        } catch (err) {
            console.warn('[Charcoal.Property.Tabulator]', 'Could not update input value:', err);

            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': this.property_label,
                    '[[ errorThrown ]]':  tabulatorWidgetL10n.data.badOutput
                })
            } ]).dispatch();
        }
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.get_input_data = function () {
        try {
            const json = this.tabulator_input.value.trim();
            if (!json) {
                return [];
            }

            return JSON.parse(json);
        } catch (err) {
            console.warn('[Charcoal.Property.Tabulator]', 'Could not retrieve input value:', err);

            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': this.property_label,
                    '[[ errorThrown ]]':  tabulatorWidgetL10n.data.badInput
                })
            } ]).dispatch();
        }

        return [];
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.get_table_data = function () {
        /**
         * Adds support for customizing the table data to be stored on the
         * property input by using one of Tabulator's row range lookups.
         */
        return this.tabulator_instance.getData(this.input_options?.storableRowRange);
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.validate_table = function () {
        return this.tabulator_instance?.validate();
    }

    Charcoal.Admin.Property_Input_Tabulator.prototype.create_tabulator = function () {
        // var lang = Charcoal.Admin.lang();

        this.tabulator_instance = new Tabulator(
            `${this.tabulator_selector}-tabulator`,
            {
                data: this.get_input_data(),
                ...this.tabulator_options
            }
        );

        const update_input_data = () => this.update_input_data();

        this.tabulator_instance.on('columnMoved', update_input_data);
        this.tabulator_instance.on('dataChanged', update_input_data);
        this.tabulator_instance.on('rowMoved',    update_input_data);

        for (const [ event, listener ] of this.tabulator_events) {
            this.tabulator_instance.on(event, listener);
        }

        /**
         * Adds support for data validation on specified callbacks.
         */
        if (Array.isArray(this.input_options?.validateOn)) {
            const validate_table = () => {
                try {
                    this.validate_table();
                } catch (err) {
                    console.warn('[Charcoal.Property.Tabulator]', 'Could not validate table on callback:', err);
                }
            };

            this.input_options.validateOn.forEach((callback) => {
                if (typeof callback !== 'string') {
                    console.error('[Charcoal.Property.Tabulator]', 'Invalid validateOn callback:', callback);
                }

                this.tabulator_instance.on(callback, validate_table);
            });
        }

        return this;
    };

    /**
     * Determines if the component is a candidate for validation.
     *
     * @param  {Component} [scope] - The parent component that calls for validation.
     * @return {boolean}
     */
    Charcoal.Admin.Property_Input_Tabulator.prototype.will_validate = function (scope) {
        return (scope && $.contains(scope.element()[0], this.element()[0]));
    };

    /**
     * Validates the property input.
     *
     * @return {boolean}
     */
    Charcoal.Admin.Property_Input_Tabulator.prototype.validate = function () {
        const validity = this.tabulator_instance.validate();

        if (validity === true) {
            return true;
        }

        const propLabel = document.querySelector('label[for="' + this.input_id.replace(/_[a-z]{2}$/, '') + '"]').textContent;

        const uniqueMessagesPerRow = {};

        validity.forEach((cellComponent) => {
            const rowComponent = cellComponent.getRow();
            const colComponent = cellComponent.getColumn();

            const colTitle = colComponent.getElement().textContent.replace(/\s+\*$/, '').trim();
            const rowIndex = (rowComponent.getIndex() ?? (rowComponent.getPosition(true) + 1));

            const fieldLabel  = `${propLabel || this.tabulator_input.name} ${colTitle} #${rowIndex}`;
            const constraints = cellComponent._getSelf().modules.validate.invalid ?? [];

            uniqueMessagesPerRow[rowIndex] ??= {};

            constraints.forEach((constraint) => {
                const message = (
                    constraint.parameters?.validationMessage ??
                    resolveTabulatorValidatorMessage(constraint) ??
                    formWidgetL10n.validation.badInput
                );

                if (uniqueMessagesPerRow?.[rowIndex]?.[message]) {
                    return;
                }

                uniqueMessagesPerRow[rowIndex][message] = true;

                Charcoal.Admin.feedback([ {
                    level:   'error',
                    message: commonL10n.errorTemplate.replaceMap({
                        '[[ errorMessage ]]': fieldLabel,
                        '[[ errorThrown ]]':  message
                    })
                } ]);
            });
        });

        return false;
    };

    const cellMenuItemActions = {
        deleteColumn: function (event, cell) {
            this.delete_column(cell.getColumn());
            this.update_input_data();
        },
        deleteRow: function (event, cell) {
            cell.getRow().delete();
            this.update_input_data();
        },
        insertColumn: function (event, cell) {
            this.add_col(cell.getColumn());
            this.update_input_data();
        },
        insertRow: function (event, cell) {
            this.add_row(cell.getRow());
            this.update_input_data();
        },
    };

    const columnMenuItemActions = {
        deleteColumn: function (event, column) {
            this.delete_column(column);
            this.update_input_data();
        },
        insertColumn: function (event, column) {
            this.add_col(column);
            this.update_input_data();
        },
    };

    const rowMenuItemActions = {
        deleteRow: function (event, row) {
            row.delete();
            this.update_input_data();
        },
        insertRow: function (event, row) {
            this.add_row(row);
            this.update_input_data();
        },
    };

    const parseAutoColumnTemplatesKeyQuery = (key) => {
        const isRE = key.match(/^\/(.*)\/([a-z]*)$/);
        if (isRE) {
            return new RegExp(isRE[1], isRE[2].indexOf('i') === -1 ? '' : 'i');
        }

        return key;
    };

    /**
     * Resolves the localized validation message for one of Tabulator's
     * built-in validators.
     *
     * @see https://github.com/olifolkerd/tabulator/blob/5.2.3/src/js/modules/Validate/defaults/validators.js
     *
     * @param  {object} constraint            - The constraint object.
     * @param  {string} constraint.type       - The constraint type.
     * @param  {*}      constraint.parameters - The constraint parameters.
     * @return {?string}
     */
    const resolveTabulatorValidatorMessage = (constraint) => {
        switch (constraint.type) {
            case 'integer':
            case 'float':
            case 'numeric': {
                return formWidgetL10n.validation.typeMismatchNumber;
            }

            case 'string': {
                return formWidgetL10n.validation.typeMismatch;
            }

            case 'max': {
                return formWidgetL10n.validation.rangeOverflow.replace('{{ max }}', constraint.parameters);
            }

            case 'min': {
                return formWidgetL10n.validation.rangeUnderflow.replace('{{ min }}', constraint.parameters);
            }

            case 'starts': {
                // starts with value
                return formWidgetL10n.validation.patternMismatchWithStart.replace('{{ format }}', constraint.parameters);
            }

            case 'ends': {
                // ends with value
                return formWidgetL10n.validation.patternMismatchWithEnd.replace('{{ format }}', constraint.parameters);
            }

            case 'maxLength': {
                return formWidgetL10n.validation.tooLong.replace('{{ max }}', constraint.parameters);
            }

            case 'minLength': {
                return formWidgetL10n.validation.tooShort.replace('{{ min }}', constraint.parameters);
            }

            case 'in': {
                // in provided value list
                return formWidgetL10n.validation.valueMissingSelect;
            }

            case 'regex': {
                return formWidgetL10n.validation.patternMismatch;
            }

            case 'unique': {
                return formWidgetL10n.validation.notUnique;
            }

            case 'required': {
                return formWidgetL10n.validation.valueMissing;
            }
        }

        return null;
    };

}(Charcoal, jQuery, document, Tabulator));
