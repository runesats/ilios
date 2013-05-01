/**
 * Client-side application code for the curriculum inventory management (cim) module.
 *
 * Defines the following namespaces:
 *     ilios.cim
 *     ilios.cim.dom
 *     ilios.cim.event
 *     ilios.cim.page
 *     ilios.cim.transaction
 *     ilios.cim.widget
 *
 *  Dependencies:
 *     application/views/scripts/ilios_base.js
 *     ilios_i18nVendor
 *     YUI Dom/Event/Element libs
 *     YUI Container libs
 */
ilios.namespace('cim.dom');
ilios.namespace('cim.event');
ilios.namespace('cim.page');
ilios.namespace('cim.transaction');
ilios.namespace('cim.widget');

/**
 * Module-level configuration.
 * @property config
 * @type {Object}
 */
ilios.cim.config = {};

/**
 * Entry point to the client-side application.
 * Initializes the page, loads the model, widgets etc.
 * @param {Object} config The module configuration.
 * @param {Number} [reportId] The Id of the report to display.
 * @method init
 *
 */
ilios.cim.page.init = function (config, reportId) {

    var Event = YAHOO.util.Event;

    // set module configuration
    ilios.cim.config = YAHOO.lang.isObject(config) ? config : {};
    reportId = reportId || false;

    // wire dialogs to buttons
    Event.addListener('search_reports_btn', 'click', function (event) {
        if (! ilios.cim.page.reportSearchDialog) { // instantiate on demand
            ilios.cim.page.reportSearchDialog = new ilios.cim.widget.ReportSearchDialog('report_search_picker');;
        }
        ilios.cim.page.reportSearchDialog.show();
        Event.stopEvent(event);
        return false;
    });

    Event.addListener('create_report_btn', 'click', function (event) {
        if (! ilios.cim.page.createReportDialog) {
            ilios.cim.page.createReportDialog = new ilios.cim.widget.CreateReportDialog('create_report_dialog', {}, config.programs);
        }
        ilios.cim.page.createReportDialog.show();
        Event.stopEvent(event);
        return false;
    });

    // wire up report details view
    if (reportId) {
        // @todo
    }
};

//
// widgets sub-module
//
(function () {

    /**
     * "Create Report" dialog.
     * @namespace ilios.cim.widget
     * @class CreateReportDialog
     * @extends YAHOO.widget.Dialog
     * @constructor
     * @param {HTMLElement|String} el The element or element-ID representing the dialog
     * @param {Object} userConfig The configuration object literal containing
     *     the configuration that should be set for this dialog.
     * @param {Object} programs a lookup object of programs, used to populate the "program" dropdown.
     */
    ilios.cim.widget.CreateReportDialog = function (el, userConfig, programs){
        var defaultConfig = {
            width: "640px",
            modal: true,
            fixedcenter: true,
            visible: false,
            hideaftersubmit: false,
            zIndex: 999,
            buttons: [
                {
                    text: ilios_i18nVendor.getI18NString('general.terms.create'),
                    handler: function () {
                        this.submit();
                    },
                    isDefault: true
                },
                {
                    text: ilios_i18nVendor.getI18NString('general.terms.cancel'),
                    handler: function () {
                        this.reset();
                        this.cancel();
                    }
                }
            ]
        };

        this.programs = YAHOO.lang.isObject(programs) ? programs : {};

        // merge the user config with the default configuration
        userConfig = userConfig || {};
        var config = YAHOO.lang.merge(defaultConfig, userConfig);

        // call the parent constructor with the merged config
        ilios.cim.widget.CreateReportDialog.superclass.constructor.call(this, el, config);

        // clear out the dialog and center it before showing it.
        this.beforeShowEvent.subscribe(function () {
            this.reset();
            this.center();
        });

        // append the program as options to the dropdown
        this.renderEvent.subscribe(function () {
            var Dom = YAHOO.util.Dom,
                key, program,
                el, parentEl;

            var parentEl = document.getElementById('new_report_program');
            for (key in this.programs) {
                if (this.programs.hasOwnProperty(key)) {
                    program = this.programs[key];
                    el = document.createElement('option');
                    Dom.setAttribute(el, 'value', program.program_id);
                    el.innerHTML = program.title;
                    parentEl.appendChild(el);
                }
            }
        });

        this.beforeSubmitEvent.subscribe(function () {
            document.getElementById('report_creation_status').innerHTML = ilios_i18nVendor.getI18NString('general.terms.creating') + '...';
    });

        /*
         * Form submission success handler.
         * @param {Object} resultObject
         */
        this.callback.success = function (resultObject) {
            var parsedResponse;
            try {
                parsedResponse = YAHOO.lang.JSON.parse(resultObject.responseText);
            } catch (e) {
                document.getElementById('report_creation_status').innerHTML
                    = ilios_i18nVendor.getI18NString('general.error.must_retry');
                return;
            }

            document.getElementById('report_creation_status').innerHTML = '';

            if (parsedResponse.hasOwnProperty('error')) {
                document.getElementById('report_creation_status').innerHTML = parsedResponse.error;
                return;
            }
            // redirect to report details view
            document.getElementById('report_creation_status').innerHTML
                = ilios_i18nVendor.getI18NString('general.terms.created') + '.';
            window.location = window.location.protocol + "//" + window.location.host + window.location.pathname
                + '?report_id=' + parsedResponse.report_id;
        };

        /*
         * Form submission error handler.
         * @param {Object} resultObject
         */
        this.callback.failure = function (resultObject) {
            ilios.global.defaultAJAXFailureHandler(resultObject);
            document.getElementById('report_creation_status').innerHTML
                = ilios_i18nVendor.getI18NString('general.error.must_retry');
        }

        // form validation function
        this.validate = function () {
            var Dom = YAHOO.util.Dom;
            var data = this.getData();
            var msgs = [];
            if ('' === YAHOO.lang.trim(data.report_name)) {
                msgs.push(ilios_i18nVendor.getI18NString('curriculum_inventory.create.validate.report_name'));
                Dom.addClass('new_report_name', 'validation-failed');
            }
            if ('' === YAHOO.lang.trim(data.report_description)) {
                msgs.push(ilios_i18nVendor.getI18NString('curriculum_inventory.create.validate.report_description'));
                Dom.addClass('new_report_description', 'validation-failed');
            }

            if (! /^[1-9][0-9]{3}$/.test(data.report_year)) {
                msgs.push(ilios_i18nVendor.getI18NString('curriculum_inventory.create.validate.report_year'));
                Dom.addClass('new_report_year', 'validation-failed');
            }
            if (msgs.length) {
                document.getElementById('report_creation_status').innerHTML = msgs.join('<br />') + '<br />';
                return false;
            }
            return true;
        };

        this.render();
    };

    // inheritance
    YAHOO.lang.extend(ilios.cim.widget.CreateReportDialog, YAHOO.widget.Dialog, {
        // clear out form, reset status field etc.
        reset : function () {
            var Dom = YAHOO.util.Dom;
            document.getElementById('report_creation_status').innerHTML = '';
            document.getElementById('new_report_name').value = '';
            document.getElementById('new_report_description').value = '';
            document.getElementById('new_report_year').value = '';
            document.getElementById('new_report_program').selectedIndex = 0;
            Dom.removeClass('new_report_name', 'validation-failed');
            Dom.removeClass('new_report_description', 'validation-failed');
            Dom.removeClass('new_report_year', 'validation-failed');

        }
    });

    /**
     * "Edit Report" dialog.
     * @namespace ilios.cim.widget
     * @class EditReportDialog
     * @extends YAHOO.widget.Dialog
     * @constructor
     * @param {HTMLElement|String} el The element or element-ID representing the dialog
     * @param {Object} userConfig The configuration object literal containing
     *     the configuration that should be set for this dialog.
     */
    ilios.cim.widget.EditReportDialog = function (el, userConfig){
        var defaultConfig = {
            width: "640px",
            modal: true,
            fixedcenter: true,
            visible: false,
            zIndex: 999,
            buttons: [
                {
                    text: ilios_i18nVendor.getI18NString('general.terms.done'),
                    handler: function () {
                        // @todo implement
                        this.cancel();
                    },
                    isDefault: true
                },
                {
                    text: ilios_i18nVendor.getI18NString('general.terms.cancel'),
                    handler: function () {
                       // @todo implement
                        this.cancel();
                    }
                }
            ]
        };

        // merge the user config with the default configuration
        userConfig = userConfig || {};
        var config = YAHOO.lang.merge(defaultConfig, userConfig);

        // call the parent constructor with the merged config
        ilios.cim.widget.EditReportDialog.superclass.constructor.call(this, el, config);

        // session model
        this.model = null;

        this.render();
    };

    // inheritance
    YAHOO.lang.extend(ilios.cim.widget.EditReportDialog, YAHOO.widget.Dialog, {
        /**
         * Sets the internal model for this dialog.
         * @method setModel
         * @param {Object} model
         */
        setModel : function (model) {
            this.model = model;
        }
    });

    /**
     * "Search Reports" dialog.
     * @namespace ilios.cim.widget
     * @class ReportSearchDialog
     * @extends YAHOO.widget.Dialog
     * @constructor
     * @param {HTMLElement|String} el The element or element-ID representing the dialog
     * @param {Object} userConfig The configuration object literal containing
     *     the configuration that should be set for this dialog.
     */
    ilios.cim.widget.ReportSearchDialog = function (el, userConfig) {

        var Event = YAHOO.util.Event;
        var KEY = YAHOO.util.KeyListener.KEY;

        var defaultConfig = {
            width: "600px",
            modal: true,
            visible: false,
            constraintoviewport: false,
            hideaftersubmit: false,
            zIndex: 999,
            buttons: [
                {
                    text: ilios_i18nVendor.getI18NString('general.terms.cancel'),
                    handler: function () {
                        this.cancel();
                    }
                },
                {
                    text: ilios_i18nVendor.getI18NString('general.phrases.search.clear'),
                    handler: function () {
                        this.emptySearchDialogForViewing();
                        return false;
                    }
                }
            ]
        };
        // merge the user config with the default configuration
        userConfig = userConfig || {};
        var config = YAHOO.lang.merge(defaultConfig, userConfig);

        // call the parent constructor with the merged config
        ilios.cim.widget.ReportSearchDialog.superclass.constructor.call(this, el, config);

        // clear out the dialog and center it before showing it.
        this.beforeShowEvent.subscribe(function () {
            this.emptySearchDialogForViewing();
            this.center();
        });

        this.beforeSubmitEvent.subscribe(function () {
            document.getElementById('report_search_status').innerHTML = ilios_i18nVendor.getI18NString('general.terms.searching') + '...';
        });

        this.validate = function () {
            var data = this.getData();
            if (ilios.lang.trim(data.report_search_term).length < 2) {
                document.getElementById('report_search_status').innerHTML
                    = ilios_i18nVendor.getI18NString('general.error.query_length');
                return false;
            }
            return true;
        };

        /*
         * Form submission success handler.
         * @param {Object} resultObject
         */
        this.callback.success = function (resultObject) {
            var Dom = YAHOO.util.Dom;
            var parsedResponse, searchResultsContainer;
            var i, n;
            var reports;
            var liEl, wrapperEl, linkEl;

            try {
                parsedResponse = YAHOO.lang.JSON.parse(resultObject.responseText);
            } catch (e) {
                document.getElementById('report_search_status').innerHTML
                    = ilios_i18nVendor.getI18NString('general.error.must_retry');
                return;
            }

            searchResultsContainer = document.getElementById('report_search_results_list');
            ilios.utilities.removeAllChildren(searchResultsContainer);
            document.getElementById('report_search_status').innerHTML = '';

            if (parsedResponse.hasOwnProperty('error')) {
                document.getElementById('report_search_status').innerHTML = parsedResponse.error;
                return;
            }

            reports = parsedResponse.reports;
            if (! reports.length) {
                document.getElementById('report_search_status').innerHTML
                    = ilios_i18nVendor.getI18NString('general.phrases.search.no_match');
            }
            for (i =0, n = reports.length; i < n; i++) {
                liEl = document.createElement('li');
                wrapperEl = document.createElement('span');
                Dom.addClass(wrapperEl, 'title');
                linkEl = document.createElement('a');
                Dom.setAttribute(linkEl, 'href', window.location.protocol + "//" + window.location.host +
                    window.location.pathname + "?report_id=" + reports[i].report_id);
                linkEl.appendChild(document.createTextNode(reports[i].name + ' (' + reports[i].year + ')'));
                wrapperEl.appendChild(linkEl);
                liEl.appendChild(wrapperEl);
                searchResultsContainer.appendChild(liEl);
            }
        };

        /*
         * Form submission error handler.
         * @param {Object} resultObject
         */
        this.callback.failure = function (resultObject) {
            ilios.global.defaultAJAXFailureHandler(resultObject);
            document.getElementById('report_search_status').innerHTML
                = ilios_i18nVendor.getI18NString('general.error.must_retry');
        }

        // wire event handlers for input field and search button
        Event.addListener('report_search_term', 'keypress', function (event, dialog) {
            var charCode = event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode);
            if (KEY.ENTER === charCode) {
                dialog.submit();
                Event.stopEvent(event);
                return false;
            }
            return true;
        }, this);

        Event.addListener('search_report_submit_btn', 'click', function (event, dialog) {
            dialog.submit();
            Event.stopEvent(event);
            return false;
        }, this);

        this.render();
    };

    // inheritance
    YAHOO.lang.extend(ilios.cim.widget.ReportSearchDialog, YAHOO.widget.Dialog, {
        emptySearchDialogForViewing: function () {
            var element = document.getElementById('report_search_results_list');
            ilios.utilities.removeAllChildren(element);
            element = document.getElementById('report_search_status');
            element.innerHTML = '';
            element = document.getElementById('report_search_term');
            element.value = '';
            element.focus();
        }
    });
}());
