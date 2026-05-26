import bbobHTML from "https://esm.sh/@bbob/html";
import presetHTML5 from "https://esm.sh/@bbob/preset-html5";

export function BBCodeRender(text) {
    return bbobHTML(text, presetHTML5());
}

export function numberFormatter(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num;
};