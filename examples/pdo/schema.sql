-- Fictional schema used only by the SchemaWeave PDO example.
CREATE TABLE demo_content (
    id INTEGER PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    summary TEXT NULL
);

CREATE TABLE demo_faq (
    id INTEGER PRIMARY KEY,
    content_id INTEGER NOT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL
);

CREATE TABLE demo_related (
    id INTEGER PRIMARY KEY,
    content_id INTEGER NOT NULL,
    related_content_id INTEGER NOT NULL
);
