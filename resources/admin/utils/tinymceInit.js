import { restoreCursorPosition, saveCursorPosition } from "./cursorSetup";

export const initTinymce = ($this, $ref, idx) => {
  tinymce.init({
    inline: true,
    menubar: false,
    target: $ref,
    plugins: 'link',
    toolbar:
      "bold italic backcolor underline | link",
    setup: (editor) => {
      editor.on("init", function () {
        tinymce.$("p", editor.getBody()).css("margin", "0");
      });
      editor.on("NodeChange", function () {
        tinymce.$("p", editor.getBody()).css("margin", "0");
      });
      editor.on("change", (event) => {
        const cursorPosition = saveCursorPosition($ref);
        if (idx !==undefined) {
            console.log('dddd')
          $this.$set($this.item.data.value, idx, editor.getContent());
        } else {
          $this.item.data.value = editor.getContent();
        }
        $this.$nextTick(() => {
          restoreCursorPosition($ref, cursorPosition);
        });
      });
      editor.on("click", (event) => {
        const mceuId = tinymce.activeEditor?.theme?.panel?._id || "mceu_7";
        const getEle = document.getElementById(mceuId);
        getEle.addEventListener("click", function (event) {
          event.stopPropagation();
        });
      });
    },
  });
};
