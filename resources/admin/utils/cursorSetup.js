export const saveCursorPosition=(element)=> {
    let selection = window.getSelection();
    let range = selection.getRangeAt(0);
    let preSelectionRange = range.cloneRange();
    preSelectionRange.selectNodeContents(element);
    preSelectionRange.setEnd(range.startContainer, range.startOffset);
    let start = preSelectionRange.toString().length;
    return {
      start: start,
      end: start + range.toString().length
    };
  }
  
  export const restoreCursorPosition =(element, savedPosition)=> {
    let charIndex = 0, range = document.createRange();
    range.setStart(element, 0);
    range.collapse(true);
    let nodeStack = [element], node, foundStart = false, stop = false;
  
    while (!stop && (node = nodeStack.pop())) {
      if (node.nodeType === 3) {
        let nextCharIndex = charIndex + node.length;
        if (!foundStart && savedPosition.start >= charIndex && savedPosition.start <= nextCharIndex) {
          range.setStart(node, savedPosition.start - charIndex);
          foundStart = true;
        }
        if (foundStart && savedPosition.end >= charIndex && savedPosition.end <= nextCharIndex) {
          range.setEnd(node, savedPosition.end - charIndex);
          stop = true;
        }
        charIndex = nextCharIndex;
      } else {
        let i = node.childNodes.length;
        while (i--) {
          nodeStack.push(node.childNodes[i]);
        }
      }
    }
  
    let sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
  }
  