# Indentation

**Priority: MEDIUM**  
**Impact: Readability and maintainability**

---

## General Rule

Use tabs for indentation. Nested elements should be indented once per level.

---

## Basic Structure

**Correct:**

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Title</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <article>
            <h1>Article Title</h1>
            <p>Article content.</p>
        </article>
    </main>
    <footer>
        <p>&copy; 2024</p>
    </footer>
</body>
</html>
```

---

## Mixed HTML and PHP

When mixing HTML and PHP (common in WordPress templates), maintain consistent indentation.

**Correct:**

```php
<?php if ( have_posts() ) : ?>
    <div class="posts">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h2><?php the_title(); ?></h2>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php endif; ?>
```

---

## Long Attribute Lists

When an element has many attributes, consider breaking to multiple lines.

**Acceptable:**

```html
<input 
    id="user-email"
    class="form-control"
    type="email"
    name="email"
    placeholder="Enter your email"
    required
    aria-describedby="email-help"
>
```

---

## Inline vs Block Elements

- **Block elements:** Always on their own line with proper indentation
- **Inline elements:** Can remain on the same line as surrounding content

**Correct:**

```html
<p>This is a paragraph with <strong>bold text</strong> and <a href="#">a link</a>.</p>

<div>
    <p>Block element content.</p>
</div>
```
