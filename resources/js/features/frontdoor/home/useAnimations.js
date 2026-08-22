import { animate, inView, stagger } from "motion";

export default function useAnimations() {
  const initAnimations = () => {
    // pengecekan aksesibilitas, lewati animasi jika user mengaktifkan "Reduce Motion"
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      document.querySelectorAll("[data-animate]").forEach((el) => {
        el.style.opacity = 1;
        el.style.transform = "translateX(0)";
        el.style.transform = "translateY(0)";
      });
      return;
    }

    const isDesktop = window.innerWidth >= 768;

    // section hero start
    // hero left
    const heroLeftItems = document.querySelectorAll("[data-animate='hero-left-item']");
    animate(
      heroLeftItems,
      {
        opacity: [0, 1],
        x: [-40, 0],
      },
      {
        duration: 0.8,
        delay: stagger(0.2, { startDelay: 0.15 }),
        ease: "easeOut",
      },
    );

    // hero right
    animate(
      "[data-animate='hero-right']",
      {
        opacity: [0, 1],
        x: [40, 0],
      },
      {
        duration: 0.8,
        delay: 0.2,
        ease: "easeOut",
      },
    );
    // section hero end

    // section services start
    // header
    inView(
      "[data-animate='services-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.3,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // cards
    if (isDesktop) {
      inView(
        "#services-grid",
        (grid) => {
          const cards = grid.querySelectorAll("[data-animate='service-card']");

          animate(
            cards,
            {
              opacity: [0, 1],
              y: [40, 0],
            },
            {
              duration: 0.7,
              delay: stagger(0.2, { startDelay: 0.4 }),
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    } else {
      inView(
        "[data-animate='service-card']",
        (card) => {
          animate(
            card,
            {
              opacity: [0, 1],
            },
            {
              duration: 0.7,
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    }

    // footer
    inView(
      "[data-animate='services-footer']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );
    // section services end

    // section why choose start
    // image
    inView(
      "[data-animate='why-choose-image']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.3,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // header
    inView(
      "[data-animate='why-choose-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.2,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // items
    if (isDesktop) {
      inView(
        "#why-choose-list",
        (container) => {
          const items = container.querySelectorAll("[data-animate='why-choose-item']");

          animate(
            items,
            {
              opacity: [0, 1],
              y: [40, 0],
            },
            {
              duration: 0.7,
              delay: stagger(0.5, { startDelay: 0.4 }),
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    } else {
      inView(
        "[data-animate='why-choose-item']",
        (item) => {
          animate(
            item,
            {
              opacity: [0, 1],
              y: [40, 0],
            },
            {
              duration: 0.7,
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    }
    // section why choose end

    // section portfolio start
    // header
    inView(
      "[data-animate='portfolio-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.3,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // items
    if (isDesktop) {
      inView(
        "#portfolio-grid",
        (container) => {
          const items = container.querySelectorAll("[data-animate='portfolio-item']");

          animate(
            items,
            {
              opacity: [0, 1],
              y: [40, 0],
              scale: [0.9, 1],
            },
            {
              duration: 0.7,
              delay: stagger(0.3, { startDelay: 0.3 }),
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    } else {
      inView(
        "[data-animate='portfolio-item']",
        (item) => {
          animate(
            item,
            {
              opacity: [0, 1],
              y: [30, 0],
              scale: [0.95, 1],
            },
            {
              duration: 0.6,
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    }
    // section portfolio end

    // section reviews start
    // header
    inView(
      "[data-animate='reviews-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.3,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // cards
    if (isDesktop) {
      inView(
        "#reviews-grid",
        (grid) => {
          const cards = grid.querySelectorAll("[data-animate='review-card']");

          animate(
            cards,
            {
              opacity: [0, 1],
              y: [40, 0],
            },
            {
              duration: 0.7,
              delay: stagger(0.3, { startDelay: 0.4 }),
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    } else {
      inView(
        "[data-animate='review-card']",
        (card) => {
          animate(
            card,
            {
              opacity: [0, 1],
              y: [40, 0],
            },
            {
              duration: 0.6,
              ease: "easeOut",
            },
          );
        },
        { amount: 0.2 },
      );
    }

    // footer
    inView(
      "[data-animate='reviews-footer']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.5,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );
    // section reviews end

    // section schedules start
    // map
    inView(
      "[data-animate='schedules-map']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            x: [-40, 0],
          },
          {
            duration: 0.8,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // header
    inView(
      "[data-animate='schedules-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [30, 0],
          },
          {
            duration: 0.6,
            delay: 0.2,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // list items
    inView(
      "#schedules-list",
      (container) => {
        const rows = container.querySelectorAll("[data-animate='schedule-row']");

        animate(
          rows,
          {
            opacity: [0, 1],
            x: [20, 0],
          },
          {
            duration: 0.5,
            delay: stagger(0.2, { startDelay: 0.3 }),
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // note
    inView(
      "[data-animate='schedules-note']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [20, 0],
          },
          {
            duration: 0.5,
            delay: 0.7,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );
    // section schedules end

    // section faq start
    // header
    inView(
      "[data-animate='faq-header']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.3,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // items
    inView(
      "#faq-list",
      (container) => {
        const items = container.querySelectorAll("[data-animate='faq-item']");

        animate(
          items,
          {
            opacity: [0, 1],
            y: [25, 0],
          },
          {
            duration: 0.6,
            delay: isDesktop ? stagger(0.12, { startDelay: 0.35 }) : stagger(0.08, { startDelay: 0.15 }),
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );

    // footer
    inView(
      "[data-animate='faq-footer']",
      (element) => {
        animate(
          element,
          {
            opacity: [0, 1],
            y: [40, 0],
          },
          {
            duration: 0.7,
            delay: 0.5,
            ease: "easeOut",
          },
        );
      },
      { amount: 0.2 },
    );
    // section faq end
  };

  return { initAnimations };
}
