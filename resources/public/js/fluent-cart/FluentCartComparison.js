import NinjaTableFluentCartUtils from "./NinjaTableFluentCartUtils";

const $ = window.jQuery;

class FluentCartComparison {

    static init($table, settings) {
        this.$table = $table;
        this.settings = settings;
        this.appendStyles();
    }

    static createComparisonModal(rows, settings) {
        if (rows.length < 2 || rows.length > 5) return alert('Select 2–5 products');
        const id = `nt_cmp_${settings.table_id}`;
        $(`#${id}`).remove();
        $('body').append(`
            <div id="${id}" class="nt_cmp_overlay">
                <div class="nt_cmp_content">
                    <div class="nt_cmp_header">
                        <h3>Compare (${rows.length})</h3>
                        <button class="nt_cmp_close">×</button>
                    </div>
                    <div class="nt_cmp_body">
                        <div id="cmp_${id}" class="loading">Loading comparison...</div>
                    </div>
                    <div class="nt_cmp_footer">
                        <button class="nt_btn nt_btn_sec" id="close_cmp">Close</button>
                    </div>
                </div>
            </div>`);
        $(`#${id}`).fadeIn();
        setTimeout(() => this.render(rows, settings, id), 80);
        $(`#${id}`).on('click', '.nt_cmp_close, #close_cmp', () => this.close(id));
        $(`#${id}`).on('click', e => e.target === e.currentTarget && this.close(id));
        $(document).on('keydown.cmp', e => e.key === 'Escape' && this.close(id));
    }

    static close(id) {
        $(`#${id}`).fadeOut(() => {
            $(`#${id}`).remove();
            $(document).off('keydown.cmp');
        });
    }

    static render(rows, settings, id) {
        let html = `
            <div class="cmp_wrap">
                <table class="nt_cmp_table">
                    <thead>
                        <tr>
                            <th class="prop">Feature</th>
                            ${rows.map(r => `<th>Product ${rows.indexOf(r) + 1}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>`;
                        (settings.columns || []).forEach(col => {
                            if (!col.visible || col.key.includes('editing')) return;
                            if (!rows.some(r => r[col.key])) return;

                            html += `<tr data-is_compare_row="true">
                                <td class="prop">${col.title}</td>`;

                            rows.forEach(r => {
                                const pid = r.cart.product_id;
                                const val = r[col.key] || '';
                                const cls = /buy|add_to_cart/.test(col.key) ? 'act' :
                                    /quantity/.test(col.key) ? 'qty' :
                                        /image/.test(col.key) ? 'img' :
                                            /price/.test(col.key) ? 'price' : 'val';
                                html += `<td class="${cls}" data-pid="${pid}" data-key="${col.key}">${val}</td>`;
                            });
                            html += `</tr>`;
                        });

                        html += `</tbody>
                </table>
            </div>`;
        $(`#cmp_${id}`).html(html);
        this.bindEvents($(`#${id}`), settings);
    }

    static bindEvents($modal, settings) {
        const $table = $modal.find('.nt_cmp_table');
        NinjaTableFluentCartUtils.init($table, settings);
    }

    static appendStyles() {
        if ($('#nt_cmp_style').length) return;
        $('head').append(`
            <style id="nt_cmp_style">
                /* Overlay & Modal */
                .nt_cmp_overlay {
                    position: fixed;
                    top: 0; left: 0;
                    width: 100%; height: 100%;
                    background: rgba(0,0,0,0.6);
                    z-index: 10000;
                    display: none;
                    overflow: auto;
                    padding: 20px;
                    box-sizing: border-box;
                }

                .nt_cmp_content {
                    margin: 40px auto;
                    background: #fff;
                    border-radius: 12px;
                    max-width: 95%;
                    width: 1200px;
                    max-height: 90vh;
                    overflow: hidden;
                    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
                    display: flex;
                    flex-direction: column;
                }

                /* Header - Compact */
                .nt_cmp_header {
                    padding: 14px 20px;
                    background: #f8f9fa;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-shrink: 0;
                }

                .nt_cmp_header h3 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: 600;
                    color: #333;
                }

                .nt_cmp_close {
                    background: none;
                    border: none;
                    font-size: 28px;
                    cursor: pointer;
                    color: #999;
                    padding: 0;
                    width: 36px;
                    height: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .nt_cmp_close:hover {
                    color: #333;
                }

                /* Body - Scrollable */
                .nt_cmp_body {
                    flex: 1;
                    overflow: auto;
                    padding: 0;
                }

                .cmp_wrap {
                    overflow: auto;
                    min-width: 800px;
                }

                /* Table Styling */
                .nt_cmp_table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                    background: #fff;
                }

                .nt_cmp_table th,
                .nt_cmp_table td {
                    padding: 12px 16px;
                    border: 1px solid #e0e0e0;
                    text-align: left;
                    vertical-align: top;
                }

                .nt_cmp_table th {
                    background: #f5f5f5;
                    font-weight: 600;
                    color: #333;
                    position: sticky;
                    top: 0;
                    z-index: 10;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .prop {
                    width: 180px;
                    min-width: 180px;
                    background: #f8f9fa !important;
                    font-weight: 600;
                    position: sticky;
                    left: 0;
                    z-index: 11;
                    box-shadow: 2px 0 5px rgba(0,0,0,0.05);
                }

                /* Cell Types */
                .act a, .act button {
                    display: inline-block;
                    padding: 8px 14px;
                    background: #a46497;
                    color: #fff;
                    border: none;
                    border-radius: 6px;
                    font-size: 13px;
                    cursor: pointer;
                    text-decoration: none;
                    font-weight: 500;
                }

                .act a:hover, .act button:hover {
                    background: #8b3f7a;
                }

                .act a.loading, .act button.loading {
                    opacity: 0.7;
                    cursor: not-allowed;
                }

                .act a.added {
                    background: #28a745;
                }

                .img img {
                    max-width: 80px;
                    max-height: 80px;
                    object-fit: cover;
                    border-radius: 6px;
                    border: 1px solid #eee;
                }

                .qty input {
                    width: 70px;
                    padding: 6px 8px;
                    text-align: center;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }

                .price {
                    font-weight: 600;
                    color: #e91e63;
                    font-size: 15px;
                }

                /* Footer */
                .nt_cmp_footer {
                    padding: 16px 20px;
                    border-top: 1px solid #eee;
                    text-align: right;
                    background: #f8f9fa;
                    flex-shrink: 0;
                }

                .nt_btn_sec {
                    padding: 10px 20px;
                    background: #6c757d;
                    color: #fff;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                }

                .nt_btn_sec:hover {
                    background: #5a6268;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .nt_cmp_content {
                        margin: 10px auto;
                        width: calc(100% - 20px);
                        max-height: 95vh;
                    }

                    .nt_cmp_header {
                        padding: 12px 16px;
                    }

                    .nt_cmp_header h3 {
                        font-size: 16px;
                    }

                    .nt_cmp_table th,
                    .nt_cmp_table td {
                        padding: 10px 12px;
                        font-size: 13px;
                    }

                    .prop {
                        width: 140px;
                        min-width: 140px;
                    }

                    .cmp_wrap {
                        min-width: 600px;
                    }
                }

                @media (max-width: 480px) {
                    .cmp_wrap {
                        min-width: 500px;
                    }

                    .act a, .act button {
                        padding: 6px 10px;
                        font-size: 12px;
                    }
                }
            </style>
        `);
    }
}

export default FluentCartComparison;
