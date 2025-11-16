import bbobHTML from "https://esm.sh/@bbob/html";
import presetHTML5 from "https://esm.sh/@bbob/preset-html5";

export function BBCodeRender(text) {
    return bbobHTML(text, presetHTML5());
}