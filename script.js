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



// 1. Theme Toggle Function
function initializeTheme() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;
    
    // Set initial theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Update icon based on current theme
    const icon = themeToggle.querySelector('i');
    if (icon) {
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Add click event
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update theme
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon
        if (icon) {
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    });
}