
(function(){

    // Simple perfect table chart
    // --------------------------

    var I18N = {
        'en': {
            "sEmptyTable":     "No data available in table",
            "sInfo":           "Showing _START_ to _END_ of _TOTAL_ entries",
            "sInfoEmpty":      "Showing 0 to 0 of 0 entries",
            "sInfoFiltered":   "(filtered from _MAX_ total entries)",
            "sInfoPostFix":    "",
            "sInfoThousands":  ",",
            "sLengthMenu":     "Show _MENU_ entries",
            "sLoadingRecords": "Loading...",
            "sProcessing":     "Processing...",
            "sSearch":         "Search:",
            "sZeroRecords":    "No matching records found",
            "oPaginate": {
                "sFirst":    "First",
                "sLast":     "Last",
                "sNext":     "Next",
                "sPrevious": "Previous"
            },
            "oAria": {
                "sSortAscending":  ": activate to sort column ascending",
                "sSortDescending": ": activate to sort column descending"
            }
        },
        'de': {
            "sProcessing":   "Bitte warten...",
            "sLengthMenu":   "Anzeige von _MENU_ Einträgen",
            "sZeroRecords":  "Keine Einträge vorhanden.",
            "sInfo":         "_START_ bis _END_ von _TOTAL_ Einträgen angezeigt",
            "sInfoEmpty":    "0 bis 0 von 0 Einträgen angezeigt",
            "sInfoFiltered": "(gefiltert von _MAX_  Einträgen)",
            "sInfoPostFix":  "",
            "sSearch":       "Suchen",
            "sUrl":          "",
            "oPaginate": {
                "sFirst":    "Erster",
                "sPrevious": "Zurück",
                "sNext":     "Weiter",
                "sLast":     "Letzter"
            }
        }
    };

    var trim = function (myString) {
        return myString.replace(/^\s+/g,'').replace(/\s+$/g,'')
    } 

    var DataTable = Datawrapper.Visualizations.DataTable = function() {

    };

    _.extend(DataTable.prototype, Datawrapper.Visualizations.Base, {

        render: function(el) {
            el = $(el);
            // add table
            var me = this, table, tr, td, th, r,
                isHighlighted = function(series) {
                    return me.chart.hasHighlight() && me.chart.isHighlighted(series);
                };
            table = $('<table id="datatable"><thead /><tbody /></table>');
            tr = $('<tr />');
            if (me.chart.hasRowHeader()) {
                var h = me.dataset.rowNameLabel();
                if (/^X\.\d+$/.test(h)) h = '';
                tr.append('<th>'+h+'</th>');
            }
            var colType = [];
            _.each(me.chart.dataSeries(), function(series) {
                th = $('<th>'+series.name+'</th>');
                if (isHighlighted(series)) {
                    th.addClass('highlight');
                }
                var number_count = 0;
                _.each(series.data, function(val) {
                    if (_.isNumber(val)){number_count ++;}
                });
                if (number_count > series.data.length/2) {
                    colType.push('number');
                    th.addClass('number');
                } else {
                    colType.push('string');
                }
                tr.append(th);
            });
            $('thead', table).append(tr);
            for (r = 0; r < me.chart.numRows(); r++) {
                tr = $('<tr />');
                var highlighted_rows = me.get('highlighted-rows');
                if (me.chart.hasRowHeader()) {
                    tr.append('<th>'+me.chart.rowLabel(r)+'</th>');
                    // Highlight the row
                    if (_.isArray(highlighted_rows) && _.indexOf(highlighted_rows, trim(me.chart.rowLabel(r))) >= 0) {
                        tr.addClass('highlight');
                    }
                } else { // Highlight the row
                         // In this case, the chart has not row header, the value of me.get('table-highlight-row')
                         // is like "Row <line number starting from 1>" (see rowLabels's definition in dw.chart.js)
                    if (_.isArray(highlighted_rows) && _.indexOf(highlighted_rows, "Row "+(me.chart.rowLabel(r)+1)) >= 0) {
                        tr.addClass('highlight');
                    }
                }
                _.each(me.chart.dataSeries(), function(series, s) {
                    var cell_content = me.chart.formatValue(series.data[r], true);
                    if (cell_content == "n/a") {
                        cell_content = "&mdash;";
                    }
                    td = $('<td>'+cell_content+'</td>');
                    if (isHighlighted(series)) {
                        td.addClass('highlight');
                    }
                    // set a type as classe
                    if (_.isNumber(series.data[r]))
                        td.addClass("number");
                    else if (cell_content == "&mdash;")
                        td.addClass("not-available");
                    else if (cell_content == "&mdash;")
                    td.attr('title', series.name);
                    tr.append(td);
                });
                $('tbody', table).append(tr);
            }
            el.append(table);

            if (me.get('table-responsive')) {
                table.addClass('responsive');
            }

            var datatable_i18n = (me.theme.meta.locale && I18N[me.theme.meta.locale.slice(0, 2)])? 
                I18N[me.theme.meta.locale.slice(0, 2)] : I18N["en"];

            // Functions to sort formated number
            jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                "formatted-num-pre": function ( a ) {
                    a = (a === "—" || a === "") ? -1 : a.replace( /[^\d\-\.]/g, "" );
                    return parseFloat( a );
                },
                "formatted-num-asc": function ( a, b ) {return a - b;},
                "formatted-num-desc": function ( a, b ) {return b - a;}
            });

            // set a list of column types for datatable.js (in order to support ordering)
            var colum_types = [];
            if (me.chart.hasRowHeader()) {colum_types.push(null);}
            _.each(colType, function(type, s) {
                if (type == "number"){
                    colum_types.push({ "sType": "formatted-num" });
                }else {
                    colum_types.push({ "sType": null });
                }

            });

            table.dataTable({
                "bPaginate" : me.get('table-paginate', false),
                "bInfo"     : me.get('table-paginate', false),
                "bFilter"   : me.get('table-filter', false),
                "bSort"     : me.get('table-sortable', false),
                "oLanguage" : datatable_i18n,
                "aoColumns": colum_types
            });

            el.append('<br style="clear:both"/>');
        }

    });


}).call(this);