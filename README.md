# RightLine Ads Extension

A WordPress plugin that adds a custom post type for ads with responsive image support and a Beaver Builder slider module.

## Features

- **Custom Post Type**: "Ads" with two types (Banner and Large Homepage)
- **Responsive Images**: Upload different image sizes for desktop (≥992px), tablet (768-991px), and mobile (<768px)
- **Beaver Builder Module**: Drag-and-drop ads slider with:
  - Fade transition (0.5s)
  - Autoplay with 3-second delay
  - Shuffle (random order on each page load)
  - Pause on hover
  - Loop playback
  - No margin or padding

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

## Image Dimensions

### Banner Ad
- Desktop (1440px): 1440 × 100 px
- Tablet (992px): 992 × 100 px
- Mobile (768px): 768 × 100 px

### Large Homepage Ad
- Desktop (1440px): 1440 × 285 px
- Tablet (992px): 992 × 285 px
- Mobile (768px): 768 × 285 px

## Usage

### Creating Ads

1. Go to **Ads > Add New** in the WordPress admin
2. Enter an ad title
3. Select the ad type (Banner or Large Homepage)
4. Upload images for each screen size (dimension labels are shown next to each upload field)
5. Publish the ad

### Using the Beaver Builder Module

1. Edit a page with Beaver Builder
2. Drag the **Ads Slider** module onto your page
3. Select the ad type you want to display
4. Save and publish

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Beaver Builder plugin (for the slider module)

## Hooks and Filters

- `rightline_ads_dimensions` - Modify image dimensions configuration
- `rightline_ads_breakpoints` - Modify responsive breakpoints
- `rightline_ads_types` - Modify available ad types
- `rightline_ads_slider_query_args` - Modify the WP_Query args for fetching ads

## Changelog

### 1.0.0
- Initial release
- Custom post type for ads
- Responsive image uploads
- Beaver Builder slider module
