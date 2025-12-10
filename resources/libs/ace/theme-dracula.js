define("ace/theme/dracula", ["require", "exports", "module", "ace/lib/dom"], function (e, t, n) {
    t.isDark = !0;
    t.cssClass = "ace-dracula";
    t.cssText = `
        .ace-dracula .ace_gutter {
            background: #1D2939;
            color: #D0D5DD;
        }
        .ace-dracula .ace_print-margin {
            width: 1px;
            background: #44475a;
        }
        .ace-dracula {
            background-color: #0C111D;
            color: #84CAFF;
        }
        .ace-dracula .ace_cursor {
            color: #f8f8f0;
        }
        .ace-dracula .ace_marker-layer .ace_selection {
            background: #44475a;
        }
        .ace-dracula .ace_marker-layer .ace_active-line {
            background: #44475a;
        }
        .ace-dracula .ace_gutter-active-line {
            background-color: #44475a;
        }
        .ace-dracula .ace_marker-layer .ace_selected-word {
            border: 1px solid #44475a;
        }
        .ace-dracula .ace_invisible {
            color: #6272a4;
        }
        .ace-dracula .ace_keyword,
        .ace-dracula .ace_meta,
        .ace-dracula .ace_storage,
        .ace-dracula .ace_storage.ace_type,
        .ace-dracula .ace_support.ace_type {
            color: #ff79c6;
        }
        .ace-dracula .ace_constant.ace_character,
        .ace-dracula .ace_constant.ace_language,
        .ace-dracula .ace_constant.ace_numeric,
        .ace-dracula .ace_constant.ace_other {
            color: #F9FAFB;
        }
        .ace-dracula .ace_paren, 
        .ace-dracula .ace_bracket, 
        .ace-dracula .ace_brace, 
        .ace-dracula .ace_punctuation {
            color: #F9FAFB;
        }
        .ace-dracula .ace_invalid {
            color: #f8f8f0;
            background-color: #ff79c6;
        }
        .ace-dracula .ace_support.ace_function {
            color: #50fa7b;
        }
        .ace-dracula .ace_string {
            color: #F9FAFB;
        }
        .ace-dracula .ace_comment {
            color: #75E0A7;
        }
        .ace-dracula .ace_entity.ace_name.ace_tag,
        .ace-dracula .ace_entity.ace_other.ace_attribute-name,
        .ace-dracula .ace_variable {
            color: #F9FAFB;
        }
        .ace-dracula .ace_indent-guide {
            background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAACCAYAAACZgbYnAAAAEklEQVQImWPQ0FD0ZXBzd/wPAAjVAoxeSgNeAAAAAElFTkSuQmCC) right repeat-y;
        }
    `;
    var r = e("../lib/dom");
    r.importCssString(t.cssText, t.cssClass, !1);
});

(function () {
    window.require(["ace/theme/dracula"], function (m) {
        if (typeof module == "object" && typeof exports == "object" && module) {
            module.exports = m;
        }
    });
})();
