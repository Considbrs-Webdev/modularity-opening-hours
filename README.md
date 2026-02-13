# Modularity OpeningHours

A opening-hours for creating Modularity modules.

## Getting Started

1. Clone this repository
2. Run `composer install` to install PHP dependencies
3. Run `npm install` to install Node dependencies
4. Run `npm run build` to build assets

## Development

- `npm run dev` - Start development server
- `npm run watch` - Watch for changes and rebuild
- `npm run build` - Build for production
- `composer test` - Run PHP tests
- `composer lint` - Lint PHP code
- `composer format` - Format PHP code

## Structure

```
├── modularity-opening-hours.php  # Main plugin file
├── source/
│   ├── php/
│   │   ├── App.php             # Application bootstrap
│   │   ├── AcfFields/          # ACF field definitions
│   │   └── Module/             # Module classes
│   │       ├── OpeningHours.php # Main module class
│   │       ├── assets/         # Module assets (icons)
│   │       └── views/          # Blade templates
│   └── sass/                   # SCSS files
├── assets/                     # Compiled assets (generated)
└── languages/                  # Translation files
```

## Creating Your Module

1. Rename `modularity-opening-hours.php` and update the plugin header
2. Update the namespace in `composer.json` and all PHP files
3. Rename `source/php/Module/OpeningHours.php` to your module name
4. Update ACF fields in `source/php/AcfFields/json/`
5. Customize views in `source/php/Module/views/`
6. Add styles in `source/sass/`

## License

MIT
