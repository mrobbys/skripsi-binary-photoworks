export default function useChoices(options = {}) {
  let choicesInstance = null;

  return {
    value: null,

    init() {
      choicesInstance = new window.Choices(this.$el, {
        searchEnabled: true,
        shouldSort: false,
        itemSelectText: "",
        ...options,
      });

      this.$el.addEventListener("change", (e) => {
        this.value = e.target.value;
      });

      this.$watch("value", (newValue) => {
        const targetValue = newValue === undefined || newValue === null ? "" : String(newValue);
        const currentValue = choicesInstance.getValue(true) || "";

        if (currentValue !== targetValue) {
          if (targetValue === "") {
            choicesInstance.removeActiveItems();
            choicesInstance.setChoiceByValue("");
          } else {
            choicesInstance.setChoiceByValue(targetValue);
          }
        }
      });
    },

    destroy() {
      if (choicesInstance) {
        choicesInstance.destroy();
        choicesInstance = null;
      }
    },
  };
}
