// Theme Manager - Handle light/dark theme switching
class ThemeManager {
  constructor() {
    this.currentTheme = 'auto'; // auto, light, dark
    this.systemPreference = 'light';
    this.init();
  }

  init() {
    // Detect system preference
    this.detectSystemPreference();

    // Load saved theme
    this.loadSavedTheme();

    // Apply initial theme
    this.applyTheme();

    // Listen for system preference changes
    this.setupSystemListener();
  }

  detectSystemPreference() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      this.systemPreference = 'dark';
    } else {
      this.systemPreference = 'light';
    }
  }

  setupSystemListener() {
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      mediaQuery.addEventListener('change', (e) => {
        this.systemPreference = e.matches ? 'dark' : 'light';
        if (this.currentTheme === 'auto') {
          this.applyTheme();
        }
      });
    }
  }

  loadSavedTheme() {
    const savedTheme = localStorage.getItem('dashboard-theme');
    if (savedTheme && ['auto', 'light', 'dark'].includes(savedTheme)) {
      this.currentTheme = savedTheme;
    }
  }

  saveTheme() {
    localStorage.setItem('dashboard-theme', this.currentTheme);
  }

  setTheme(theme) {
    if (!['auto', 'light', 'dark'].includes(theme)) {
      console.warn('Invalid theme:', theme);
      return;
    }

    this.currentTheme = theme;
    this.saveTheme();
    this.applyTheme();

    // Dispatch theme change event
    this.dispatchThemeChange();
  }

  applyTheme() {
    const body = document.body;
    const effectiveTheme = this.getEffectiveTheme();

    // Remove existing theme classes
    body.classList.remove('theme-light', 'theme-dark');

    // Add new theme class
    body.classList.add(`theme-${effectiveTheme}`);

    // Update meta theme-color for mobile browsers
    this.updateMetaThemeColor(effectiveTheme);

    // Update any theme toggle buttons
    this.updateThemeToggles();
  }

  getEffectiveTheme() {
    if (this.currentTheme === 'auto') {
      return this.systemPreference;
    }
    return this.currentTheme;
  }

  updateMetaThemeColor(theme) {
    let themeColor = '#FFFFFF'; // Light theme default

    if (theme === 'dark') {
      themeColor = '#1C1C1E'; // Dark theme default
    }

    // Update existing meta tag or create new one
    let metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (!metaThemeColor) {
      metaThemeColor = document.createElement('meta');
      metaThemeColor.name = 'theme-color';
      document.head.appendChild(metaThemeColor);
    }
    metaThemeColor.content = themeColor;
  }

  updateThemeToggles() {
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    toggles.forEach(toggle => {
      const targetTheme = toggle.getAttribute('data-theme-toggle');
      toggle.classList.toggle('active', this.currentTheme === targetTheme);

      // Update ARIA attributes
      toggle.setAttribute('aria-pressed', this.currentTheme === targetTheme);
    });
  }

  dispatchThemeChange() {
    const event = new CustomEvent('themechange', {
      detail: {
        theme: this.currentTheme,
        effectiveTheme: this.getEffectiveTheme()
      }
    });
    window.dispatchEvent(event);
  }

  toggle() {
    const effectiveTheme = this.getEffectiveTheme();
    const newTheme = effectiveTheme === 'light' ? 'dark' : 'light';
    this.setTheme(newTheme);
  }

  // Create theme toggle button
  createToggleButton(options = {}) {
    const button = document.createElement('button');
    button.className = options.className || 'btn btn-secondary theme-toggle';
    button.setAttribute('aria-label', 'Toggle theme');
    button.setAttribute('title', 'Toggle light/dark theme');

    button.innerHTML = `
      <i class="fas fa-moon theme-icon-dark"></i>
      <i class="fas fa-sun theme-icon-light"></i>
    `;

    button.addEventListener('click', () => {
      this.toggle();
    });

    return button;
  }

  // Create theme selector dropdown
  createThemeSelector(options = {}) {
    const container = document.createElement('div');
    container.className = options.className || 'theme-selector';

    container.innerHTML = `
      <label class="form-label">Theme</label>
      <select class="form-select theme-select">
        <option value="auto">Auto (System)</option>
        <option value="light">Light</option>
        <option value="dark">Dark</option>
      </select>
    `;

    const select = container.querySelector('.theme-select');
    select.value = this.currentTheme;

    select.addEventListener('change', (e) => {
      this.setTheme(e.target.value);
    });

    // Update select when theme changes
    window.addEventListener('themechange', () => {
      select.value = this.currentTheme;
    });

    return container;
  }

  // Get current theme info
  getThemeInfo() {
    return {
      current: this.currentTheme,
      effective: this.getEffectiveTheme(),
      system: this.systemPreference,
      isDark: this.getEffectiveTheme() === 'dark',
      isLight: this.getEffectiveTheme() === 'light',
      isAuto: this.currentTheme === 'auto'
    };
  }
}
