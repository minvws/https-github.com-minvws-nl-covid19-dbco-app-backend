/**
 * Will copy a rendered version of the html to the clipboard
 */
export const copyHtmlToClipboard = (html: string) => {
    const clipboard = document.createElement('div');
    const body = document.body;

    clipboard.className = 'clipboard';
    clipboard.style.position = 'absolute';
    clipboard.style.left = '-999rem';
    clipboard.setAttribute('aria-hidden', 'true');
    clipboard.innerHTML = html;
    body.appendChild(clipboard);

    selectElementContents(clipboard);

    document.execCommand('Copy');
    body.removeChild(clipboard);
};

/**
 * Selects the contents of an element
 */
const selectElementContents = (el: HTMLDivElement) => {
    // Some MS compatibility
    const body = document.body as HTMLBodyElement & { createTextRange: () => any };

    let range, sel;
    if (document.createRange && window.getSelection) {
        range = document.createRange();
        sel = window.getSelection();

        if (!sel) return false;

        sel.removeAllRanges();
        try {
            range.selectNodeContents(el);
            sel.addRange(range);
        } catch (e) {
            range.selectNode(el);
            sel.addRange(range);
        }

        return true;
    } else if (body.createTextRange) {
        range = body.createTextRange();
        range.moveToElementText(el);
        range.select();

        return true;
    }

    return false;
};
