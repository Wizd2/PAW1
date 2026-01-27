-- ClickClick auth pages for CMS (page_list)
INSERT INTO page_list (page_title, page_content, status) VALUES
('auth', '<section class="card"><h2>Panel użytkownika</h2><a class="btn" href="index.php?idp=login">Logowanie</a><a class="btn" href="index.php?idp=register">Rejestracja</a><a class="btn secondary" href="index.php?idp=forgot">Przypomnij hasło</a></section>', 1),
('login', '<section class="card"><h2>Logowanie</h2><form method="post" action="login_action.php"><label>Login</label><input type="text" name="login" required><label>Hasło</label><input type="password" name="password" required><button class="btn">Zaloguj</button></form></section>', 1),
('register', '<section class="card"><h2>Rejestracja</h2><form method="post" action="register_action.php"><label>Login</label><input type="text" name="login" required><label>Hasło</label><input type="password" name="password" required><button class="btn">Zarejestruj</button></form></section>', 1),
('forgot', '<section class="card"><h2>Przypomnij hasło</h2><form method="post"><label>Email</label><input type="email" name="email"><button class="btn">Wyślij</button></form></section>', 1);
