# Ikiraha Dashboard

A fully responsive, Apple-inspired dashboard/control panel built with pure HTML, CSS, and vanilla JavaScript.

## Features

- **Apple-inspired Design**: Clean typography, generous spacing, subtle shadows, and translucency
- **Responsive Layout**: Desktop sidebar navigation, mobile bottom navigation
- **Role-based Access**: Super Admin, Merchant, and Accountant roles with different permissions
- **Dark/Light Theme**: Automatic system preference detection with manual toggle
- **Modular Architecture**: Reusable components and clean separation of concerns
- **Accessibility**: WCAG AA compliant with keyboard navigation and screen reader support

## Getting Started

### Quick Start

1. Open `login.html` in your browser
2. Select a role (Super Admin, Merchant, or Accountant)
3. Use the pre-filled credentials to log in
4. Explore the dashboard features

### Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@ikiraha.com | admin123 |
| Merchant | merchant@ikiraha.com | merchant123 |
| Accountant | accountant@ikiraha.com | accountant123 |

### File Structure

```
dashboard/
├── index.html              # Main dashboard page
├── login.html              # Login page
├── css/
│   ├── variables.css       # Design system variables
│   ├── base.css           # Base styles and typography
│   ├── components.css     # Reusable UI components
│   ├── layout.css         # Navigation and layout
│   ├── pages.css          # Page-specific styles
│   └── responsive.css     # Mobile responsiveness
├── js/
│   ├── app.js             # Main application logic
│   ├── router.js          # SPA routing
│   ├── auth.js            # Authentication manager
│   └── components/
│       ├── navigation.js   # Navigation component
│       ├── toast.js       # Toast notifications
│       ├── modal.js       # Modal dialogs
│       ├── theme.js       # Theme management
│       └── data-service.js # Data fetching service
└── data/
    └── dashboard.json     # Mock dashboard data
```

## Role-Based Features

### Super Admin
- Full system access
- User and merchant management
- System analytics and reports
- All financial data access

### Merchant
- Product and menu management
- Order management
- Business analytics
- Profile management

### Accountant
- Transaction management
- Financial reports
- Data export capabilities
- Analytics access

## Technical Details

### Design System

The dashboard uses a comprehensive design system with:
- **Colors**: Apple-inspired color palette with primary blue (#007AFF)
- **Typography**: System fonts with clear hierarchy
- **Spacing**: Consistent 4px base unit scaling
- **Shadows**: Subtle depth with multiple shadow levels
- **Border Radius**: Rounded corners (2xl radius for cards)
- **Transitions**: Smooth 120-180ms animations

### Responsive Breakpoints

- **Desktop**: 1200px+ (expanded sidebar)
- **Tablet**: 769px-1024px (standard sidebar)
- **Mobile**: 768px and below (bottom navigation)
- **Small Mobile**: 480px and below (compact layout)

### Browser Support

- Modern browsers with ES6 module support
- Chrome 61+, Firefox 60+, Safari 11+, Edge 16+

## Development

### Adding New Pages

1. Create page content method in `app.js`
2. Add route in `setupRoutes()`
3. Add navigation item in `navigation.js`
4. Create page-specific styles in `pages.css`

### Adding New Components

1. Create component file in `js/components/`
2. Export as ES6 module
3. Import and initialize in `app.js`
4. Add component styles in `components.css`

### Customizing Themes

Edit `css/variables.css` to customize:
- Colors and gradients
- Typography and spacing
- Shadows and effects
- Animation timings

## Production Deployment

1. Serve files from a web server (required for ES6 modules)
2. Configure proper MIME types for `.js` files
3. Enable HTTPS for production use
4. Optimize images and assets
5. Configure CSP headers for security

## API Integration

The dashboard is designed for easy API integration:

1. Update `DataService` to call real endpoints
2. Replace mock authentication in `AuthManager`
3. Add proper error handling and loading states
4. Implement real-time updates with WebSockets

## Accessibility

- Semantic HTML structure
- ARIA labels and roles
- Keyboard navigation support
- High contrast ratios
- Reduced motion support
- Screen reader compatibility

## License

This dashboard is part of the Ikiraha project.
