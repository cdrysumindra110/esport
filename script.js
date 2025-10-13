document.addEventListener("scroll", function () {
  const containerTop = document.getElementById("logo-container-top");
  const containerBottom = document.getElementById("logo-container-bottom");

  if (!containerTop || !containerBottom) return; // Check if elements exist

  const containerTopRect = containerTop.getBoundingClientRect();
  const scrollTop = window.scrollY || document.documentElement.scrollTop;

  // Check if the top container is fully out of view
  if (scrollTop > containerTopRect.bottom) {
    containerBottom.classList.add("fixed");
    containerBottom.classList.remove("sticky");
    containerTop.style.display = "none"; // Hide the top container when scrolling down
  } else {
    containerBottom.classList.remove("fixed");
    containerBottom.classList.add("sticky");
    containerTop.style.display = "block"; // Show the top container when at the top
  }
});


const toggle = document.querySelector('.bb8-toggle__checkbox');

toggle.addEventListener('change', () => {
  document.body.classList.toggle('dark-mode');
});


