export const assetUrl = (path) => {
    return window.ninja_table_admin && window.ninja_table_admin.asset_url + `${path}`;
}

export const imageUrl = (path) => {
    return window.ninja_table_admin && window.ninja_table_admin.img_url + `${path}`;
}

export const copyToClipboard = (text, message)=>{
    // Create a temporary element
    const tempElement = document.createElement('textarea');
    tempElement.value = text;
    document.body.appendChild(tempElement);

    let msg = message ? message : 'Shortcode Copied';

    // Select the text content
    tempElement.select();
    tempElement.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Access $message through the global Vue app
    if (window.ninjaApp && window.ninjaApp.$message) {
        window.ninjaApp.$message({
            message: msg,
            type: 'success'
        });
    } else {
        console.log(msg); // Fallback if $message is not available
    }
    
    document.body.removeChild(tempElement);
}

