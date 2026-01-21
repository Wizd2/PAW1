-- ClickClick: usuwa dolny blok 'grid2' z strony glownej w bazie (zostawia Kategorie + Best-sellery)
-- Wymaga MySQL 8.0+ (REGEXP_REPLACE). Jesli masz starszy MySQL, najprosciej: ponownie zaimportuj lab6_page_list_template.sql.
USE moja_strona;

UPDATE page_list
SET page_content = REGEXP_REPLACE(page_content, '<div class="grid2"[\\s\\S]*?</div>\\s*', '')
WHERE page_title = 'glowna';
