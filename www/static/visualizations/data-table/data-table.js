
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
                tr.append('<th>'+h+'</tr>');
            }
            var colType = [];
            _.each(me.chart.dataSeries(), function(series) {
                th = $('<th>'+series.name+'</th>');
                if (isHighlighted(series)) {
                    th.addClass('highlight');
                }
                /*if (series.type.substr(0,14) == 'number-decimal') {
                    colType.push('number-decimal');
                    th.addClass('number-decimal');
                } else if (series.type == 'number') {*/
                    // check for small numbers
                var small = true;
                _.each(series.data, function(val) {
                    small = small && val <= 100 && val >= -100;
                });
                colType.push('number'+(small ? '-small' : ''));
                th.addClass('number'+(small ? '-small' : ''));
                //}

                tr.append(th);
            });
            $('thead', table).append(tr);
            for (r = 0; r < me.chart.numRows(); r++) {
                tr = $('<tr />');
                var highlighted_rows = me.get('highlighted-rows');
                if (me.chart.hasRowHeader()) {
                    tr.append('<th>'+me.chart.rowLabel(r)+'</th>');
                    // Highlight the row
                    if (_.isArray(highlighted_rows) && _.indexOf(highlighted_rows, me.chart.rowLabel(r)) >= 0) {
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
                    td.addClass(colType[s]);
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
            
            table.dataTable({
                "bPaginate" : me.get('table-paginate', false),
                "bInfo"     : me.get('table-paginate', false),
                "bFilter"   : me.get('table-filter', false),
                "bSort"     : me.get('table-sortable', false),
                "oLanguage" : datatable_i18n
            });

            el.append('<br style="clear:both"/>');
        }

    });

}).call(this);