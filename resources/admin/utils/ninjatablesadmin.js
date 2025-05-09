export const assetUrl = (path) => {
    return window.ninja_table_admin && window.ninja_table_admin.asset_url + `${path}`;
}

export const imageUrl = (path) => {
    return window.ninja_table_admin && window.ninja_table_admin.img_url + `${path}`;
}

