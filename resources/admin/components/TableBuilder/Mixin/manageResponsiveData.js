export const manageResponsiveData = {
    data() {
        return {
            headerInfo: null,
            formattedResponsiveData: null,
            responsiveInitial: {
                showHeader: false,
                itemsPerRow: 1,
                tableData: {},
                tableDataLength: null,
                cell_direction: 'row',
                mobileActivePixel: 400,
                tabletActivePixel: 750,
            },
        };
    },
    methods: {
        calculateColSpan(style) {
            if (this.responsiveInitial.showHeader && this.responsiveInitial.cell_direction === 'row') {
                return style.rowspan;
            } else if (this.responsiveInitial.showHeader && this.responsiveInitial.cell_direction === 'column') {
                return style.colspan;
            } else {
                return 1;
            }
        },
        calculateRowSpan(style) {
            if (this.responsiveInitial.showHeader && this.responsiveInitial.cell_direction === 'row') {
                return style.colspan;
            } else if (this.responsiveInitial.showHeader && this.responsiveInitial.cell_direction === 'column') {
                return style.rowspan;
            } else {
                return 1;
            }
        },
        makeResponsiveDataFormat() {
            const mainTableData = this.deepClone(this.tableData.data);
            const mainTableDataHeaders = this.deepClone(this.tableData.headers);
            let processedData = {};
            let dataIndex = 0;

            for (let i = 0; i < mainTableData.length; i++) {
                mainTableDataHeaders.forEach((item, key) => {
                    let lastColumnIndex = mainTableDataHeaders.length - 1 === key ? key : null;
                    let columnIndex = item.slice(7);
                    processedData['data_' + dataIndex] = mainTableData[i].rows[item];
                    processedData['data_' + dataIndex].rowIndex = i;
                    processedData['data_' + dataIndex].columnIndex = columnIndex;
                    processedData['data_' + dataIndex].rowStyle = mainTableData[i].style;
                    processedData['data_' + dataIndex].lastColumnIndex = lastColumnIndex;
                    dataIndex++;
                })
            }

            let headerData = {};
            for (let j = 0; j < mainTableDataHeaders.length; j++) {
                headerData['data_' + j] = processedData['data_' + j];
            }

            this.headerInfo = headerData;
            this.formattedResponsiveData = processedData;
        },
        getResponsiveTableData(cellDirection, header, itemsPerRow, tabChanged) {
            if (this.selectedDevice !== 'mobile' && this.selectedDevice !== 'tablet') {
                return {};
            }

            tabChanged ? this.makeResponsiveDataFormat() : '';

            const headerDataLength = Object.keys(this.headerInfo).length;
            const rowWiseDataLength = Object.keys(this.formattedResponsiveData).length;
            let records = {};
            let recordIndex = 0;

            if (header && cellDirection === 'row') {
                return this.headerAndCellDirectionRow(itemsPerRow, headerDataLength, rowWiseDataLength);
            } else if (header && cellDirection === 'column') {
                return this.headerAndCellDirectionColumn(itemsPerRow, headerDataLength);
            } else { //for topRowAsHeader false

                /* Note: when we merged two or more cells, then some cells rowspan & colspan values become 0.
                   by default, it will take a free space.
                   So for avoiding them we filter them again
                 */
                let dataInfo = {};
                let dataInfoIndex = 0;
                const totalTableRow = this.tableData.table.tr;
                if (cellDirection === 'row') {
                    for (let item = 0; item < rowWiseDataLength; item++) {
                        if (this.formattedResponsiveData['data_' + item].style.rowspan !== 0 && this.formattedResponsiveData['data_' + item].style.colspan !== 0) {
                            dataInfo['data_' + dataInfoIndex] = this.formattedResponsiveData['data_' + item];
                            dataInfoIndex++;
                        }
                    }
                } else {
                    for (let header = 0; header < headerDataLength; header++) {
                        for (let row = 0; row < totalTableRow; row++) {
                            let index = header + (row * headerDataLength);
                            if (this.formattedResponsiveData['data_' + index].style.rowspan !== 0 && this.formattedResponsiveData['data_' + index].style.colspan !== 0) {
                                dataInfo['data_' + dataInfoIndex] = this.formattedResponsiveData['data_' + index];
                                dataInfoIndex++;
                            }
                        }
                    }
                }

                const recordLength = Object.keys(dataInfo).length;
                for (let i = 0; i < recordLength;) { //responsive processed data
                    let singleRow = {}
                    for (let col = 0; col < itemsPerRow; col++) {
                        if (dataInfo['data_' + i]) {
                            singleRow['data_' + col] = dataInfo['data_' + i];
                            i++;
                        }
                    }
                    records[recordIndex] = singleRow;
                    recordIndex++;
                }
            }
            return records;
        },
        headerAndCellDirectionRow(itemsPerRow, headerDataLength, rowWiseDataLength) {
            let records = {};
            let recordIndex = 0;
            if (Number(this.tableData.table.tr) === 1) {
                return this.responsiveSingleRow(headerDataLength);
            } else {
                let totalGroup = Math.ceil((rowWiseDataLength - headerDataLength) / (itemsPerRow * headerDataLength));
                for (let group = 0; group < totalGroup; group++) {
                    for (let header = 0; header < headerDataLength; header++) {
                        let singleRow = {};
                        for (let col = 0; col <= itemsPerRow; col++) {
                            if (col == 0) {
                                singleRow["data_" + col] = this.formattedResponsiveData["data_" + header];
                            } else {
                                let index = (group * itemsPerRow * headerDataLength) + (col * headerDataLength) + header
                                if (this.formattedResponsiveData["data_" + index] && this.formattedResponsiveData["data_" + index].style.rowspan !== 0 && this.formattedResponsiveData["data_" + index].style.colspan !== 0) {
                                    singleRow["data_" + col] = this.formattedResponsiveData["data_" + index];
                                }
                            }
                        }
                        records[recordIndex] = singleRow;
                        recordIndex++;
                    }
                }
            }
            this.responsiveInitial.tableDataLength = Object.keys(records).length;
            return records;
        },
        headerAndCellDirectionColumn(itemsPerRow, headerDataLength) {
            let records = {};
            let recordIndex = 0;
            if (Number(this.tableData.table.tr) === 1) {
                return this.responsiveSingleRow(headerDataLength);
            } else {
                let startingIndex = headerDataLength;
                let totalGroup = Math.ceil((this.tableData.table.tr - 1) / itemsPerRow);
                for (let group = 0; group < totalGroup; group++) {
                    for (let item = 0; item <= itemsPerRow; item++) {
                        if (!this.formattedResponsiveData["data_" + startingIndex]) {
                            break;
                        }
                        let singleRow = {};
                        for (let header = 0; header < headerDataLength; header++) {
                            if (item === 0) {
                                singleRow["data_" + header] = this.formattedResponsiveData["data_" + header];
                            } else {
                                if (this.formattedResponsiveData["data_" + startingIndex].style.rowspan > 0 && this.formattedResponsiveData["data_" + startingIndex].style.colspan > 0) {
                                    singleRow["data_" + header] = this.formattedResponsiveData["data_" + startingIndex];
                                }
                                startingIndex++;
                            }
                        }
                        records[recordIndex] = singleRow;
                        recordIndex++;
                    }
                }
            }
            this.responsiveInitial.tableDataLength = Object.keys(records).length;
            return records;
        },
        responsiveSingleRow(headerDataLength) {
            let records = {};
            let recordIndex = 0;
            for (let header = 0; header < headerDataLength; header++) {
                let singleRow = {};
                if (this.headerInfo["data_" + header]) {
                    singleRow["data_" + header] = this.headerInfo["data_" + header];
                }
                records[recordIndex] = singleRow;
                recordIndex++;
            }
            return records;
        },
        bottomBorderResponsive(index, cell_direction, itemsPerRow, devices) {
            const rowNumber = Number(index) + 1;
            const headerLength = this.deepClone(this.tableData.headers).length;

            if (cell_direction === 'column' && rowNumber % (itemsPerRow + 1) === 0 && this.tableData.table.tr > 1 && rowNumber != this.responsiveInitial.tableDataLength) {
                return {
                    borderBottom: (this.selectedDevice === 'tablet' ? devices.tablet.cell_border.value : devices.mobile.cell_border.value) + 'px solid black'
                }
            } else if (cell_direction === 'row' && rowNumber % headerLength === 0 && rowNumber != this.responsiveInitial.tableDataLength) {
                return {
                    borderBottom: (this.selectedDevice === 'tablet' ? devices.tablet.cell_border.value : devices.mobile.cell_border.value) + 'px solid black'
                }
            }
        },
    },
    computed: {
        responsiveDevice() {
            return this.initialData.responsive.mode_options.options.devices;
        },
        responsiveIsEnabled() {
            const responsiveEnable = this.responsive.general.options.enable_responsive_table.value;
            return this.getBoolean(responsiveEnable);
        },
        mobileDeviceShowHeader() {
            const mobileHeaderEnable = this.responsiveDevice.mobile.top_row_as_header.value;
            return this.getBoolean(mobileHeaderEnable);
        },
        tabletDeviceShowHeader() {
            const tabletHeaderEnable = this.responsiveDevice.tablet.top_row_as_header.value;
            return this.getBoolean(tabletHeaderEnable);
        },
        mobileDeviceItemsPerRow() {
            return this.responsiveDevice.mobile.items_per_row.value;
        },
        tabletDeviceItemsPerRow() {
            return this.responsiveDevice.tablet.items_per_row.value;
        },
        mobileDeviceBreakpoint() {
            const breakpointEnable = this.responsiveDevice.mobile.disable_breakpoint.value;
            return this.getBoolean(breakpointEnable);
        },
        tabletDeviceBreakpoint() {
            const breakpointEnable = this.responsiveDevice.tablet.disable_breakpoint.value;
            return this.getBoolean(breakpointEnable);
        },
        mobileDeviceCellDirection() {
            return this.responsiveDevice.mobile.cell_direction.value;
        },
        tabletDeviceCellDirection() {
            return this.responsiveDevice.tablet.cell_direction.value;
        },
        pixelBarValue: {
            get() {
                return this.selectedDevice === 'mobile' ? this.responsiveInitial.mobileActivePixel : this.responsiveInitial.tabletActivePixel
            },
            set(newValue) {
                this.selectedDevice === 'mobile' ? this.responsiveInitial.mobileActivePixel = newValue : this.responsiveInitial.tabletActivePixel = newValue
            }
        },
        showPixelSlider() {
            /*
            * This slider is only shown in responsive mode. (Mobile & Tablet)
            * When admin are in mobile or tablet mode then he/she can check ta responsive design in different width.
            */
            if (this.selectedDevice === 'mobile' && !this.mobileDeviceBreakpoint) {
                return {
                    preview: true,
                    min: 300,
                    max: 699
                }
            } else if (this.selectedDevice === 'tablet' && !this.tabletDeviceBreakpoint) {
                return {
                    preview: true,
                    min: 700,
                    max: 1023
                }
            } else {
                return {
                    preview: false
                }
            }
        },
    }
}
