import tippy from "tippy.js";
import "tippy.js/dist/tippy.css";

export function tooltipDirective(Alpine) {
  Alpine.directive('tooltip', (el, { expression }, { evaluate, cleanup }) => {
    const content = evaluate(expression);
    
    if(!content) return;
    
    const instance = tippy(el, {
      content: content,
      trigger: "mouseenter click focus",
      placement: "top",
      maxWidth: 320,
    });

    cleanup(() => instance.destroy());
  });
}

export function tableActionDropdown() {
    return {
        tippyInstance: null,
        closeDropdown() {
            if (this.tippyInstance) this.tippyInstance.hide();
        },
        init() {
            this.$nextTick(() => {
                this.tippyInstance = tippy(this.$refs.btn, {
                    content: this.$refs.dropdown,
                    interactive: true,
                    trigger: 'click',
                    placement: 'bottom-end',
                    appendTo: 'parent',
                    popperOptions: {
                        strategy: 'fixed',
                    },
                    arrow: false,
                    theme: 'custom',
                    offset: [0, 5],
                    onClickOutside: (instance) => instance.hide(),
                });
            });
        },
        destroy() {
            if (this.tippyInstance) {
                this.tippyInstance.destroy();
            }
        }
    };
}
