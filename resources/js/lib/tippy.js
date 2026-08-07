import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

export default function tooltipDirective(Alpine) {
  Alpine.directive('tooltip', (el, { expression }, { evaluate, cleanup }) => {
    const content = evaluate(expression);
    
    const instance = tippy(el, {
      content: content,
      trigger: "mouseenter click focus",
      placement: "top",
      maxWidth: 320,
    });

    cleanup(() => instance.destroy());
  });
}
