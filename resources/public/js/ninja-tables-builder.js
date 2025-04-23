(function ($) {

    /*
         * when the admin is already logged into his site then he can see the edit button in the frontend page
         * @param {string} id - table id
    */
    function editButtonStyle(id) {
        let onlyTableId = id.slice(20, id.length);
        const containerId = $('.ninja_tables_builder_class_' + onlyTableId);
        const tableId = $('#ninja_table_builder_' + onlyTableId);
        const findEditClass = $('.ntb_edit_table_class_' + onlyTableId);
        const emptyMarginLeft = tableId.css("margin-left") ? tableId.css("margin-left").slice(0, -2) : 0;
        const marginLeft = (containerId.offset().left - tableId.offset().left) + parseInt(emptyMarginLeft);
        $(findEditClass).attr('style', ' margin-left: ' + marginLeft + 'px !important');
    }

    const timeoutLimit = 300;

    $(document).ready(function ($) {
        $('.ntb_table_wrapper').each(function () {
            let mainTable = $(this)[0]
            let desktopFormat = mainTable.innerHTML

            // Get responsive information from data-responsive attr,
            let responsive = JSON.parse(mainTable.dataset.responsive)
            let isResponsive = responsive.general.options.enable_responsive_table.value

            // Get responsive settings devices
            let settingsDevices = responsive.responsive_settings.options.devices;

            if (isResponsive === true || isResponsive === 'true') {

                const tableId = mainTable.id
                const rows = $('#' + tableId + ' table')[0].rows

                // Get the length of th, td
                const columnLength = $('#' + tableId + ' table').find('tr').length
                const totalData = $('#' + tableId + ' table tr').find('td').length
                const headerLen = Math.ceil(totalData / columnLength)
                const dataLen = totalData - headerLen

                let rowSerialData = rowWiseSerialDataStructure(rows);
                // This columnTopRowData is preparing only for column wise top row header design.
                let columnTopRowData = columnWiseTopRowHeaderDesignData(rows)

                //These variables are to handle unnecessary function calling.
                let mob = false;
                let tab = false;
                let desk = false;

                let windowSize = $(window).width();

                // Call function if window reloaded
                if (windowSize <= 700) {
                    mobileDevice(mob, "mobile")
                } else if (windowSize > 700 && windowSize < 1024) {
                    tabletDevice(tab, "tablet")
                } else {
                    desktopDevice(desk)
                }

                // Call function if window resized
                $(window).resize(function () {

                    windowSize = $(window).width();

                    if (windowSize <= 700) {
                        mobileDevice(mob, "mobile")
                    } else if (windowSize > 700 && windowSize < 1024) {
                        tabletDevice(tab, "tablet")
                    } else {
                        desktopDevice(desk)
                    }
                });


                /*
                 * All conditions for mobile responsive will check from here
                 * By checking conditions it call several functions depends on conditions.
                 *
                 * @param {boolean} mob - If already mobile responsive design applied
                 *      then it will be true, and it won't call function again though window resized.
                 * @param {number} width - 80% of the window screen to generate table.
                 * @param {string} deviceName - To added tr class by device names.
                 * @return {void}
                 */
                function mobileDevice(mob, deviceName) {

                    if (mob === 'false' || mob === false) {
                        mob = true
                        tab = false
                        desk = false

                        // Getting data to check responsive options for mobile
                        const mobileData = responsive.mode_options.options.devices.mobile
                        const breakpoint = mobileData.disable_breakpoint.value
                        const topRowAsHeader = mobileData.top_row_as_header.value
                        const itemsPerRow = mobileData.items_per_row.value;
                        const cellBorder = mobileData.cell_border.value
                        const cellDirection = mobileData.cell_direction.value;

                        const mobileCellPadding = settingsDevices.mobile.mobile_cell_padding.value;
                        const mobileTableAlign = settingsDevices.mobile.mobile_table_alignment.value;

                        if (breakpoint === 'false' || breakpoint === false) {
                            $('#' + tableId + ' table tbody').empty()
                            if (cellDirection === 'row') {
                                if (Object.keys(rows).length > 1) {
                                    rowWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, mobileCellPadding);
                                } else {
                                    if (topRowAsHeader === 'true' || topRowAsHeader === true) {
                                        rowWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, mobileCellPadding);
                                    } else {
                                        rowWiseStaticRowDesign(itemsPerRow, deviceName, mobileCellPadding)
                                    }
                                }
                            } else {
                                if (Object.keys(rows).length > 1) {
                                    columnWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, mobileCellPadding);
                                } else {
                                    if (topRowAsHeader === 'true' || topRowAsHeader === true) {
                                        columnWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, mobileCellPadding);
                                    } else {
                                        columnWiseStaticRowDesign(itemsPerRow, deviceName, mobileCellPadding);
                                    }
                                }
                            }
                            responsiveTableAlign(mobileTableAlign)
                        }
                    } else {
                        desktopDevice(desk)
                    }

                    /*
                       * editButtonStyle function is used for style the edit button in the frontend page
                       * (when the admin is already logged into his site)
                    */
                    editButtonStyle(mainTable.id)
                }

                /*
                 * All conditions for tablet responsive will check from here
                 * By checking conditions it call several functions depends on conditions.
                 *
                 * @param {boolean} tab - If already tablet responsive design applied
                 *      then it will be true, and it won't call function again though window resized.
                 * @param {number} width - 80% of the window screen to generate table.
                 * @param {string} deviceName - To added tr class by device names.
                 * @return {void}
                 */
                function tabletDevice(tab, width, deviceName) {

                    if (tab === 'false' || tab === false) {
                        mob = false
                        tab = true
                        desk = false

                        // Getting data to check responsive options for tab
                        const tabData = responsive.mode_options.options.devices.tablet
                        const breakpoint = tabData.disable_breakpoint.value
                        const topRowAsHeader = tabData.top_row_as_header.value
                        const itemsPerRow = tabData.items_per_row.value;
                        const cellBorder = tabData.cell_border.value
                        const cellDirection = tabData.cell_direction.value;
                        const tabletCellPadding = settingsDevices.tablet.tablet_cell_padding.value;
                        const tabletTableAlign = settingsDevices.tablet.tablet_table_alignment.value;

                        if (breakpoint === 'false' || breakpoint === false) {
                            $('#' + tableId + ' table tbody').empty()
                            if (cellDirection === 'row') {
                                if (Object.keys(rows).length > 1) {
                                    rowWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, tabletCellPadding);
                                } else {
                                    if (topRowAsHeader === 'true' || topRowAsHeader === true) {
                                        rowWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, tabletCellPadding)
                                    } else {
                                        rowWiseStaticRowDesign(itemsPerRow, deviceName, tabletCellPadding)
                                    }
                                }
                            } else {
                                if (Object.keys(rows).length > 1) {
                                    columnWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, tabletCellPadding);
                                } else {
                                    if (topRowAsHeader === 'true' || topRowAsHeader === true) {
                                        columnWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, tabletCellPadding);
                                    } else {
                                        columnWiseStaticRowDesign(itemsPerRow, deviceName, tabletCellPadding);
                                    }
                                }
                            }
                            responsiveTableAlign(tabletTableAlign)
                        } else {
                            desktopDevice(desk)
                        }
                    }


                    /*
                       * editButtonStyle function is used for style the edit button in the frontend page
                       * (when the admin is already logged into his site)
                    */
                    editButtonStyle(mainTable.id)
                }

                /*
                 * Desktop view is as our general view so not need to do anything here.
                 * It will generate table from our old data which is stored into 'desktopFormate'.
                 *
                 * @param {boolean} desktopView - If already in desktop then  it will be true and
                 *      although window is resized it won't call function again.
                 * @return {void}
                 */
                function desktopDevice(desktopView) {
                    if (desktopView === 'false' || desktopView === false) {
                        mob = false
                        tab = false
                        desk = true
                        $('#' + tableId)[0].innerHTML = desktopFormat
                    }

                    /*
                       * editButtonStyle function is used for style the edit button in the frontend page
                       * (when the admin is already logged into his site)
                    */
                    editButtonStyle(mainTable.id)
                }

                /*
                 * This function will generate table for static row design by row direction for mobile and tablet
                 *
                 * @param {number} itemsPerRow - Number of items in a single row
                 * @param {number} width - 80% of the window screen will apply to generate table
                 * @param {string} deviceName - To added tr class by device names
                 * @param {number} cellPadding - Different cell padding for different devices
                 * @return {void}
                 */
                function rowWiseStaticRowDesign(itemsPerRow, deviceName, cellPadding) {
                    let tdWidth = '';
                    setTimeout(function() {
                        const dynamicWrapper = mainTable.dataset.ninja_table_builder_instance;
                        let wrapperWidth = $('.' + dynamicWrapper).width() * 0.9;
                        tdWidth = (wrapperWidth / itemsPerRow) + 'px';
                    }, timeoutLimit);

                    // Data restructured for static row design by row direction
                    // let rowWiseData = rowWiseStaticRowDesignData();

                    // Reconstruct the table with restructured data
                    let numberOfRow = Math.ceil(rowSerialData.tdCounter / itemsPerRow)
                    let td = 0;
                    for (let row = 0; row < numberOfRow; row++) {
                        $('#' + tableId + ' table tbody').append('<tr></tr>')
                        let tableRow = $('#' + tableId + ' table tbody tr')
                        $(tableRow[row]).addClass('tr_class_' + deviceName + '_' + row)
                        $(tableRow[row]).prop('id', 'tr_id_' + deviceName + '_' + row)

                        let addedClass = tableRow.addClass('staticRow')
                        for (let col = 0; col < itemsPerRow; col++) {
                            if (rowSerialData.rowsTd[td] !== undefined) {
                                let insertedTd = addedClass[row].insertCell()
                                insertedTd.innerHTML = rowSerialData.rowsTd[td].innerHTML
                                $(insertedTd).prop('id', rowSerialData.tdIds[td])
                                $(insertedTd).addClass(rowSerialData.tdClasses[td])
                                $(insertedTd).attr("style", rowSerialData.trStyles[td] + " " + rowSerialData.tdStyles[td])
                                $(insertedTd).css({
                                    "width": tdWidth,
                                    "min-width": tdWidth,
                                    "max-width": tdWidth,
                                    "box-sizing": 'border-box',
                                    "padding": cellPadding + 'px',
                                })
                            }
                            td++
                        }
                    }
                }

                /*
                 * This function will generate table for static row design by column direction for mobile and tablet
                 *
                 * @param {number} itemsPerRow - Number of items in a single row
                 * @param {number} width - 80% of the window screen will apply to generate table
                 * @param {string} deviceName - To added tr class by device names
                 * @param {number} cellPadding - Different cell padding for different devices
                 * @return {void}
                 */
                function columnWiseStaticRowDesign(itemsPerRow, deviceName, cellPadding) {
                    let tdWidth = '';
                    setTimeout(function() {
                        const dynamicWrapper = mainTable.dataset.ninja_table_builder_instance;
                        let wrapperWidth = $('.' + dynamicWrapper).width() * 0.9;
                        tdWidth = (wrapperWidth / itemsPerRow) + 'px';
                    }, timeoutLimit);

                    // Data restructured for static row design by column direction
                    let columnWiseData = columnWiseSerialDataStructure(totalData, headerLen);
                    let numberOfRow = Math.ceil(columnWiseData.tdCounter / itemsPerRow)
                    let td = 0;

                    // Reconstruct the table with restructured data
                    for (let row = 0; row < numberOfRow; row++) {
                        $('#' + tableId + ' table tbody').append('<tr></tr>')
                        let tableRow = $('#' + tableId + ' table tbody tr')
                        $(tableRow[row]).addClass('tr_class_' + deviceName + '_' + row)
                        $(tableRow[row]).prop('id', 'tr_id_' + deviceName + '_' + row)
                        let addedClass = tableRow.addClass('staticRow')
                        for (let col = 0; col < itemsPerRow; col++) {
                            if (columnWiseData.colsTd[td] !== undefined) {
                                let insertedTd = addedClass[row].insertCell()
                                insertedTd.innerHTML = columnWiseData.colsTd[td]
                                $(insertedTd).prop('id', columnWiseData.tdIds[td])
                                $(insertedTd).addClass(columnWiseData.tdClasses[td])
                                $(insertedTd).attr("style", columnWiseData.trStyles[td] + " " + columnWiseData.tdStyles[td])
                                $(insertedTd).css({
                                    "width": tdWidth,
                                    "min-width": tdWidth,
                                    "max-width": tdWidth,
                                    "box-sizing": 'border-box',
                                    "padding": cellPadding + 'px',
                                })
                            }
                            td++
                        }
                    }
                }

                /*
                 * This function will generate table for top row as header design by row direction for mobile and tablet
                 *
                 * @param {number} itemsPerRow - Number of items besides header
                 * @param {number} width - 80% of the window screen will apply to generate table
                 * @param {string} deviceName - To added tr class by device names
                 * @param {cellBorder} deviceName - To added border bottom as group separator.
                 * @param {number} cellPadding - Different cell padding for different devices
                 * @return {void}
                 */
                function rowWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, cellPadding) {
                    let tdWidth = '';
                    setTimeout(function() {
                        const dynamicWrapper = mainTable.dataset.ninja_table_builder_instance;
                        let wrapperWidth = $('.' + dynamicWrapper).width() * 0.9;
                        tdWidth = (wrapperWidth / (itemsPerRow + 1)) + 'px';
                    }, timeoutLimit);

                    //Row wise Data restructured as user requirement
                    let rowWiseData = rowWiseTopRowHeaderData(itemsPerRow);

                    // Reconstruct table with respect to restructured data
                    for (let [indTr, tRows] of Object.entries(rowWiseData.td)) {
                        $('#' + tableId + ' table tbody').append('<tr></tr>')
                        let tableRow = $('#' + tableId + ' table tbody tr')
                        $(tableRow[indTr]).addClass('tr_class_' + deviceName + '_' + indTr)
                        $(tableRow[indTr]).prop('id', 'tr_id_' + deviceName + '_' + indTr)
                        let addedClass = tableRow.addClass('topRowAsHeader')

                        for (let indTd = 0; indTd < Object.keys(tRows).length; indTd++) {
                            if (tRows[indTd] !== undefined) {
                                let insertedTd = addedClass[indTr].insertCell()
                                insertedTd.innerHTML = tRows[indTd].innerHTML
                                $(insertedTd).prop('id', rowWiseData.tdIds[indTr][indTd])
                                $(insertedTd).addClass(rowWiseData.tdClasses[indTr][indTd])
                                $(insertedTd).attr("style", rowWiseData.tdStyle[indTr][indTd])
                                $(insertedTd).prop("colspan", rowWiseData.rowSpan[indTr][indTd])
                                $(insertedTd).prop("rowspan", rowWiseData.colSpan[indTr][indTd])
                                $(insertedTd).css({
                                    "width": tdWidth,
                                    "min-width": tdWidth,
                                    "max-width": tdWidth,
                                    "box-sizing": 'border-box',
                                    "padding": cellPadding + 'px',
                                })
                            }
                        }

                        // Add cell border bottom
                        const rowNumber = Number(indTr) + 1
                        const rowLength = Object.entries(rowWiseData.td).length
                        if (rowNumber % headerLen === 0 && rowNumber !== rowLength) {
                            let insertedRow = addedClass[indTr]
                            $(insertedRow).css("border-bottom", cellBorder + "px solid black")
                        }
                    }
                }

                /*
                 * This function will generate table for top row as header design by column direction for mobile and tablet
                 *
                 * @param {number} itemsPerRow - Number of row after header.
                 * @param {number} width - 80% of the window screen will apply to generate table.
                 * @param {string} deviceName - To added tr class by device names.
                 * @param {number} cellBorder - To added border bottom as group separator.
                 * @param {number} cellPadding - Different cell padding for different devices.
                 * @return {void}
                 */
                function columnWiseTopRowHeaderDesign(itemsPerRow, deviceName, cellBorder, cellPadding) {
                    let tdWidth = '';
                    setTimeout(function() {
                        const dynamicWrapper = mainTable.dataset.ninja_table_builder_instance;
                        let wrapperWidth = $('.' + dynamicWrapper).width() * 0.9;
                        tdWidth = (wrapperWidth / headerLen) + 'px';
                    }, timeoutLimit);

                    let totalHeader = Math.ceil(dataLen / (itemsPerRow * headerLen))
                    let dataIndex = 0;
                    let insertRow = 0;
                    let singleRow = 0;

                    for (let group = 0; group < totalHeader; group++) {

                        let headerIndex = 0;

                        for (let row = 0; row < itemsPerRow + 1; row++) {
                            if (columnTopRowData.tableBody.innerHtml[dataIndex]) {

                                let isLastRow = false;

                                // row per group including header
                                $('#' + tableId + ' table tbody').append('<tr></tr>')
                                let tableRow = $('#' + tableId + ' table tbody tr')
                                $(tableRow[singleRow]).addClass('tr_class_' + deviceName + '_' + singleRow)
                                $(tableRow[singleRow]).prop('id', 'tr_id_' + deviceName + '_' + singleRow)
                                let addedClass = tableRow.addClass('topRowAsHeader')

                                for (let rowData = 0; rowData < headerLen; rowData++) {

                                    let insertedTd = addedClass[insertRow].insertCell();

                                    if (row === 0) {

                                        const dataStyle = columnTopRowData.tableHeader.tdStyles[headerIndex] + columnTopRowData.tableHeader.trStyles[headerIndex];

                                        insertedTd.innerHTML = columnTopRowData.tableHeader.innerHtml[headerIndex];
                                        $(insertedTd).prop('id', columnTopRowData.tableHeader.tdIds[headerIndex]);
                                        $(insertedTd).addClass(columnTopRowData.tableHeader.tdClasses[headerIndex]);
                                        $(insertedTd).attr("style", dataStyle);
                                        $(insertedTd).prop("colspan", columnTopRowData.tableHeader.colSpan[headerIndex]);
                                        $(insertedTd).prop("rowspan", columnTopRowData.tableHeader.rowSpan[headerIndex]);

                                        headerIndex++;
                                    } else {

                                        const dataStyle = columnTopRowData.tableBody.tdStyles[dataIndex] + columnTopRowData.tableBody.trStyles[dataIndex];

                                        insertedTd.innerHTML = columnTopRowData.tableBody.innerHtml[dataIndex];
                                        $(insertedTd).prop('id', columnTopRowData.tableBody.tdIds[dataIndex]);
                                        $(insertedTd).addClass(columnTopRowData.tableBody.tdClasses[dataIndex]);
                                        $(insertedTd).attr("style", dataStyle);
                                        $(insertedTd).prop("colspan", columnTopRowData.tableBody.colSpan[dataIndex]);
                                        $(insertedTd).prop("rowspan", columnTopRowData.tableBody.rowSpan[dataIndex]);

                                        dataIndex++;
                                    }

                                    // This condition will check is there any data in next row
                                    if (!columnTopRowData.tableBody.innerHtml[dataIndex + 1]) {
                                        isLastRow = true;
                                    }

                                    $(insertedTd).css({
                                        "width": tdWidth,
                                        "min-width": tdWidth,
                                        "max-width": tdWidth,
                                        "box-sizing": 'border-box',
                                        "padding": cellPadding + 'px',
                                    });
                                }
                                insertRow++

                                // Add cell border bottom
                                if (insertRow % (itemsPerRow + 1) === 0 && !isLastRow) {
                                    let insertedRow = addedClass[insertRow - 1]
                                    $(insertedRow).css("border-bottom", cellBorder + "px solid black")
                                }
                            }
                            singleRow++;
                        }
                    }
                }


                /*
                 * This function will return the structured data which is serially stored in an object.
                 *
                 * @param {object} rows - Total data of table by row direction.
                 * @return {object} rowWiseData
                 */
                function rowWiseSerialDataStructure(rows) {
                    let rowSerialData = {
                        allRow: {},
                        trStyles: {},
                        tdStyles: {},
                        rowsTd: {},
                        rowSpan: {},
                        colSpan: {},
                        tdIds: {},
                        tdClasses: {},
                        tdCounter: 0,
                    }
                    let index = 0

                    $(rows).each(function (rowIndex, tr) {
                        rowSerialData.allRow[rowIndex] = Object.assign(tr);
                        $(tr).find('td').each(function (columnIndex, td) {
                            rowSerialData.rowsTd[index] = Object.assign(td);
                            rowSerialData.trStyles[index] = $(rowSerialData.allRow[rowIndex]).attr('style')
                            rowSerialData.tdStyles[index] = $(rowSerialData.rowsTd[index]).attr('style')
                            rowSerialData.rowSpan[index] = $(td).attr('rowspan')
                            rowSerialData.colSpan[index] = $(td).attr('colspan')
                            rowSerialData.tdIds[index] = $(td).attr('id')
                            rowSerialData.tdClasses[index] = $(td).attr('class')
                            index++
                        });
                    });
                    rowSerialData.tdCounter = index;
                    return rowSerialData;
                }

                /*
                 * This function will return the row wise structured data for top row as header.
                 *
                 * @param {number} itemsPerRow - Total items per row besides header.
                 * @return {object} reStructuredTopRowHeaderData
                 */
                function rowWiseTopRowHeaderData(itemsPerRow) {

                    const totalGroup = Math.ceil(dataLen / (itemsPerRow * headerLen))
                    let i = 0;
                    let reStructuredTopRowHeaderData = {
                        td: {},
                        tdIds: {},
                        tdClasses: {},
                        tdStyle: {},
                        rowSpan: {},
                        colSpan: {}
                    }

                    for (let group = 0; group < totalGroup; group++) {
                        for (let header = 0; header < headerLen; header++) {
                            let tempReStructureData = {}
                            let tempReStructuredTdIds = {}
                            let tempReStructuredTdClasses = {}
                            let tempReStructureDataStyle = {}
                            let tempReStructureRowSpan = {}
                            let tempReStructureColSpan = {}
                            for (let col = 0; col <= itemsPerRow; col++) {
                                let index = (group * itemsPerRow * headerLen) + (col * headerLen) + header

                                // 'rowSerialData' is defined on the outside of this function,
                                // and it is accessible from every function.
                                if (col === 0) {
                                    tempReStructureData[col] = rowSerialData.rowsTd[header]
                                    tempReStructureDataStyle[col] = rowSerialData.trStyles[header] + " " + rowSerialData.tdStyles[header]
                                    tempReStructureRowSpan[col] = rowSerialData.rowSpan[header]
                                    tempReStructureColSpan[col] = rowSerialData.colSpan[header]
                                    tempReStructuredTdIds[col] = rowSerialData.tdIds[header]
                                    tempReStructuredTdClasses[col] = rowSerialData.tdClasses[header]
                                } else {
                                    tempReStructureData[col] = rowSerialData.rowsTd[index]
                                    tempReStructureDataStyle[col] = rowSerialData.trStyles[index] + " " + rowSerialData.tdStyles[index]
                                    tempReStructureRowSpan[col] = rowSerialData.rowSpan[index]
                                    tempReStructureColSpan[col] = rowSerialData.colSpan[index]
                                    tempReStructuredTdIds[col] = rowSerialData.tdIds[index]
                                    tempReStructuredTdClasses[col] = rowSerialData.tdClasses[index]
                                }
                            }
                            reStructuredTopRowHeaderData.td[i] = Object.assign(tempReStructureData)
                            reStructuredTopRowHeaderData.tdStyle[i] = Object.assign(tempReStructureDataStyle)
                            reStructuredTopRowHeaderData.rowSpan[i] = Object.assign(tempReStructureRowSpan)
                            reStructuredTopRowHeaderData.colSpan[i] = Object.assign(tempReStructureColSpan)
                            reStructuredTopRowHeaderData.tdIds[i] = Object.assign(tempReStructuredTdIds)
                            reStructuredTopRowHeaderData.tdClasses[i] = Object.assign(tempReStructuredTdClasses)
                            i++
                        }
                    }

                    return reStructuredTopRowHeaderData;
                }

                /*
                 * This function will return the column wise structured data for static column design.
                 *
                 * @param {number} totalData - Total length of td.
                 * @param {number} headerLen - Length of header(th) data row.
                 * @return {object} columnSerialData
                 */
                function columnWiseSerialDataStructure(totalData, headerLen) {

                    let columnSerialData = {
                        allRow: {},
                        colsTd: {},
                        trStyles: {},
                        tdStyles: {},
                        rowSpan: {},
                        colSpan: {},
                        tdIds: {},
                        tdClasses: {},
                        tdCounter: 0,
                    }
                    let i = 0;

                    // 'rowSerialData' is defined on the outside of this function,
                    // and it is accessible from every function
                    for (let row = 0; row < headerLen; row++) {
                        columnSerialData.allRow[row] = rowSerialData.allRow[row];
                        for (let col = row; col < totalData; col = col + headerLen) {
                            columnSerialData.colsTd[i] = rowSerialData.rowsTd[col].innerHTML
                            columnSerialData.trStyles[i] = rowSerialData.trStyles[col]
                            columnSerialData.tdStyles[i] = rowSerialData.tdStyles[col]
                            columnSerialData.rowSpan[i] = rowSerialData.rowSpan[col]
                            columnSerialData.colSpan[i] = rowSerialData.colSpan[col]
                            columnSerialData.tdIds[i] = rowSerialData.tdIds[col]
                            columnSerialData.tdClasses[i] = rowSerialData.tdClasses[col]
                            columnSerialData.tdCounter++
                            i++;
                        }
                    }
                    return columnSerialData;
                }

                /*
                * This function will return the column wise structured data for top row as header.
                *
                * @param {object} rows - Total data of table by row direction.
                * @return {object} - columnTopRowHeaderData
                 */
                function columnWiseTopRowHeaderDesignData(rows) {
                    let data = {
                        td: {},
                        innerHtml: {},
                        tdStyles: {},
                        trStyles: {},
                        rowSpan: {},
                        colSpan: {},
                        tdIds: {},
                        tdClasses: {},
                        index: 0,
                    }
                    let columnTopRowHeaderData = {
                        allRow: {},
                        tableHeader: JSON.parse(JSON.stringify(data)),
                        tableBody: JSON.parse(JSON.stringify(data)),
                    }

                    let thIndex = 0;
                    let tdIndex = 0;

                    $(rows).each(function (rowIndex, tr) {
                        columnTopRowHeaderData.allRow[rowIndex] = Object.assign(tr);
                        $(tr).find('td').each(function (columnIndex, td) {
                            if (rowIndex === 0) {
                                columnTopRowHeaderData.tableHeader.td[thIndex] = td;
                                columnTopRowHeaderData.tableHeader.innerHtml[thIndex] = td.innerHTML;
                                columnTopRowHeaderData.tableHeader.trStyles[thIndex] = $(columnTopRowHeaderData.allRow[rowIndex]).attr('style')
                                columnTopRowHeaderData.tableHeader.tdStyles[thIndex] = $(columnTopRowHeaderData.tableHeader.td[thIndex]).attr('style')
                                columnTopRowHeaderData.tableHeader.colSpan[thIndex] = $(td).attr('colspan')
                                columnTopRowHeaderData.tableHeader.tdIds[thIndex] = $(td).attr('id')
                                columnTopRowHeaderData.tableHeader.tdClasses[thIndex] = $(td).attr('class')
                                columnTopRowHeaderData.tableHeader.index++
                                thIndex++
                            } else {
                                columnTopRowHeaderData.tableBody.td[tdIndex] = td;
                                columnTopRowHeaderData.tableBody.innerHtml[tdIndex] = td.innerHTML;
                                columnTopRowHeaderData.tableBody.trStyles[tdIndex] = $(columnTopRowHeaderData.allRow[rowIndex]).attr('style')
                                columnTopRowHeaderData.tableBody.tdStyles[tdIndex] = $(columnTopRowHeaderData.tableBody.td[tdIndex]).attr('style')
                                columnTopRowHeaderData.tableBody.rowSpan[tdIndex] = $(td).attr('rowspan')
                                columnTopRowHeaderData.tableBody.colSpan[tdIndex] = $(td).attr('colspan')
                                columnTopRowHeaderData.tableBody.tdIds[tdIndex] = $(td).attr('id')
                                columnTopRowHeaderData.tableBody.tdClasses[tdIndex] = $(td).attr('class')
                                columnTopRowHeaderData.tableBody.index++
                                tdIndex++
                            }
                        });
                    });
                    return columnTopRowHeaderData;
                }


                /*
                 * This function will apply css to align the table.
                 *
                 * @param {string} alignment - alignment option of the table.
                 * @return {void}
                 */
                function responsiveTableAlign(alignment) {
                    if (alignment === "left") {
                        $('#' + tableId + ' table').css({
                            "margin-left": " 0",
                            "margin-right": "auto"
                        })
                    } else if (alignment === "right") {
                        $('#' + tableId + ' table').css({
                            "margin-left": "auto",
                            "margin-right": "0"
                        })
                    } else {
                        $('#' + tableId + ' table').css({
                            "margin-left": "auto",
                            "margin-right": "auto"
                        })
                    }
                }
            }
        })
    });
})(jQuery);
