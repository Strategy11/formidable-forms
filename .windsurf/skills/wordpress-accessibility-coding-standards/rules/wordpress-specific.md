# WordPress-Specific Accessibility

**Priority: HIGH**  
**Impact: WordPress ecosystem compatibility**

---

## Admin Accessibility

WordPress admin must be usable by people with disabilities.

### Admin Notices

```php
<div class="notice notice-success is-dismissible">
    <p><?php esc_html_e( 'Settings saved.', 'textdomain' ); ?></p>
</div>
```

### Screen Reader Text

```php
<span class="screen-reader-text">
    <?php esc_html_e( 'Edit this item', 'textdomain' ); ?>
</span>
```

```css
.screen-reader-text {
    border: 0;
    clip: rect(1px, 1px, 1px, 1px);
    clip-path: inset(50%);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute;
    width: 1px;
    word-wrap: normal !important;
}
```

---

## Theme Accessibility

### Skip Links

Required in accessible themes:

```php
<a class="skip-link screen-reader-text" href="#primary">
    <?php esc_html_e( 'Skip to content', 'theme-textdomain' ); ?>
</a>
```

### Navigation Landmarks

```php
<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'theme-textdomain' ); ?>">
    <?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
</nav>
```

### Search Form

```php
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="search-field" class="screen-reader-text">
        <?php esc_html_e( 'Search for:', 'theme-textdomain' ); ?>
    </label>
    <input type="search" id="search-field" class="search-field" 
           placeholder="<?php esc_attr_e( 'Search...', 'theme-textdomain' ); ?>" 
           value="<?php echo get_search_query(); ?>" name="s">
    <button type="submit" class="search-submit">
        <span class="screen-reader-text"><?php esc_html_e( 'Search', 'theme-textdomain' ); ?></span>
    </button>
</form>
```

---

## Form Accessibility

### WordPress Forms

```php
<p>
    <label for="comment"><?php esc_html_e( 'Comment', 'textdomain' ); ?></label>
    <textarea id="comment" name="comment" required aria-required="true"></textarea>
</p>
```

### Required Fields

```php
<label for="email">
    <?php esc_html_e( 'Email', 'textdomain' ); ?>
    <span class="required" aria-hidden="true">*</span>
    <span class="screen-reader-text"><?php esc_html_e( '(required)', 'textdomain' ); ?></span>
</label>
<input type="email" id="email" name="email" required aria-required="true">
```

---

## Media Accessibility

### Images in Content

```php
<?php 
$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
?>
<img src="<?php echo esc_url( $image_url ); ?>" 
     alt="<?php echo esc_attr( $alt_text ); ?>">
```

### Embedded Media

```php
<figure>
    <video controls>
        <source src="video.mp4" type="video/mp4">
        <track kind="captions" src="captions.vtt" srclang="en" label="English">
    </video>
    <figcaption><?php esc_html_e( 'Video description', 'textdomain' ); ?></figcaption>
</figure>
```

---

## Testing in WordPress

- **Keyboard navigation:** Tab through entire admin/frontend
- **Screen reader:** Test with NVDA, JAWS, or VoiceOver
- **Color contrast:** Use browser dev tools or WAVE
- **Zoom:** Test at 200% zoom
- **Automated:** Use axe, WAVE, or Lighthouse
