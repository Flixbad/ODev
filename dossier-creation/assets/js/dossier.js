(() => {
  const topbar = document.querySelector(".topbar");
  const reveals = document.querySelectorAll(".reveal");

  const onScroll = () => {
    if (!topbar) return;
    topbar.classList.toggle("is-scrolled", window.scrollY > 20);
  };

  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.16, rootMargin: "0px 0px -8% 0px" },
    );
    reveals.forEach((el) => io.observe(el));
  } else {
    reveals.forEach((el) => el.classList.add("is-visible"));
  }

  // Active nav highlight
  const sections = [...document.querySelectorAll("section[id]")];
  const links = [...document.querySelectorAll(".nav a")];

  const setActive = () => {
    const y = window.scrollY + 120;
    let current = sections[0]?.id;
    for (const section of sections) {
      if (section.offsetTop <= y) current = section.id;
    }
    links.forEach((link) => {
      const href = link.getAttribute("href")?.slice(1);
      link.style.color = href === current ? "var(--ink)" : "";
      link.style.fontWeight = href === current ? "700" : "";
    });
  };

  window.addEventListener("scroll", setActive, { passive: true });
  setActive();
})();
