-- ClickClick : SQL template
CREATE DATABASE IF NOT EXISTS moja_strona CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moja_strona;

DROP TABLE IF EXISTS page_list;
CREATE TABLE page_list (
 id INT AUTO_INCREMENT PRIMARY KEY,
 page_title VARCHAR(255) NOT NULL,
 page_content TEXT NOT NULL,
 status INT NOT NULL DEFAULT 1,
 UNIQUE KEY uniq_page_title (page_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO page_list (page_title, page_content, status) VALUES ('glowna', ' <section class="card intro">
 <h2>ClickClick</h2>
 <p>Sklep z częściami do klawiatur mechanicznych: bazy, switche, keycapy, kable i akcesoria.</p>
 </section>

 <div style="height:14px;"></div>

 <section class="card" style="margin-top:14px;">
 <h3>Kategorie</h3>
 <p>Wybierz dział i przejdź do produktów.</p>
 <div class="categoryGrid">
 <a class="categoryCard" href="index.php?idp=switches">
 <div class="categoryTitle">Switche</div>
 <div class="categoryMeta">Linear / Tactile / Clicky</div>
 </a>
 <a class="categoryCard" href="index.php?idp=keycaps">
 <div class="categoryTitle">Keycapy</div>
 <div class="categoryMeta">PBT / ABS / Artisan</div>
 </a>
 <a class="categoryCard" href="index.php?idp=cables">
 <div class="categoryTitle">Kable</div>
 <div class="categoryMeta">USB‑C / Aviator / Coiled</div>
 </a>
 <a class="categoryCard" href="index.php?idp=catalog">
 <div class="categoryTitle">Wszystkie produkty</div>
 <div class="categoryMeta">Szybki przegląd sklepu</div>
 </a>
 </div>
 </section>

 <section class="card" style="margin-top:14px;">
 <h3>Best‑sellery</h3>
 <p>Najpopularniejsze propozycje z naszej oferty.</p>
 <div class="productGrid">
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Tactile Pro 67g</h3>
 <div class="productMeta">Actuation 2.0 mm • Travel 4.0 mm</div>
 </div>
 <button class="speakerBtn" disabled title="Odsłuch ">🔊</button>
 </div>
 <div class="productImg"><img src="img/switch1.jpg" alt="Switch - Tactile" /></div>
 <div class="productBody">
 <div class="tagRow"><span class="tag">TACTILE</span><span class="tag">THOCKY</span></div>
 <div class="priceRow"><span class="price">19,90 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>PBT Keycaps — Neon Set</h3>
 <div class="productMeta">Profile: Cherry • 135 keys</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku">⌁</div>
 </div>
 <div class="productImg"><img src="img/keycaps1.jpg" alt="Keycaps" /></div>
 <div class="productBody">
 <div class="tagRow"><span class="tag">PBT</span><span class="tag">CHERRY</span></div>
 <div class="priceRow"><span class="price">149,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Coiled Cable USB‑C</h3>
 <div class="productMeta">Aviator • 1.8 m • PET sleeve</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku">⌁</div>
 </div>
 <div class="productImg"><img src="img/cable1.jpg" alt="Coiled cable" /></div>
 <div class="productBody">
 <div class="tagRow"><span class="tag">COILED</span><span class="tag">AVIATOR</span></div>
 <div class="priceRow"><span class="price">89,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Aluminium Case 65%</h3>
 <div class="productMeta">Mount: gasket • Color: black</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku">⌁</div>
 </div>
 <div class="productImg"><img src="img/case1.jpg" alt="Keyboard case" /></div>
 <div class="productBody">
 <div class="tagRow"><span class="tag">CASE</span><span class="tag">65%</span></div>
 <div class="priceRow"><span class="price">329,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>
 </div>
 </section>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('catalog', ' <section class="card cardHover clearfix">
 <img class="floatImg" src="img/case1.jpg" alt="Keyboard case" />
 <h2>Sklep</h2>
 <p>
 To jest szybki katalog ClickClick —  a później dynamicznie z bazy danych.
 Poniższe sekcje pokazują typowy układ sklepu: kategorie + lista produktów.
 </p>
 <div class="subnav" aria-label="Filtry ">
 <span class="pill">Kategorie</span>
 <span class="pill">Best‑sellery</span>
 <span class="pill">Nowości</span>
 <span class="pill">Promo</span>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card">
 <h3>Kategorie</h3>
 <div class="categoryGrid" style="margin-top:10px;">
 <a class="categoryCard" href="switches"><div class="categoryTitle">Switche</div><div class="categoryMeta">Linear / Tactile / Clicky</div></a>
 <a class="categoryCard" href="keycaps"><div class="categoryTitle">Keycapy</div><div class="categoryMeta">PBT / ABS / Artisan</div></a>
 <a class="categoryCard" href="cables"><div class="categoryTitle">Kable</div><div class="categoryMeta">USB‑C / Aviator / Coiled</div></a>
 <a class="categoryCard" href="#" onclick="return false;"><div class="categoryTitle">Stabilizatory</div><div class="categoryMeta">Screw‑in / Plate‑mount</div></a>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card">
 <h3>Wybrane produkty</h3>
 <p>Mini‑lista  — w będzie pobierana z DB i liczona z VAT.</p>
 <div class="productGrid">
 <article class="productCard">
 <div class="productHeader"><div><h3>Tactile Pro 67g</h3><div class="productMeta">Switch • 2.0 mm • 4.0 mm</div></div><button class="speakerBtn" disabled title="Odsłuch ">🔊</button></div>
 <div class="productImg"><img src="img/switch1.jpg" alt="Tactile switch" /></div>
 <div class="productBody"><div class="tagRow"><span class="tag">TACTILE</span><span class="tag">THOCKY</span></div><div class="priceRow"><span class="price">19,90 zł</span><span class="smallBtn">Dodaj </span></div></div>
 </article>

 <article class="productCard">
 <div class="productHeader"><div><h3>PBT Cherry Set</h3><div class="productMeta">Keycapy • PBT • Dye‑sub</div></div><div class="speakerBtn" title="Bez dźwięku">⌁</div></div>
 <div class="productImg"><img src="img/keycaps1.jpg" alt="PBT keycaps" /></div>
 <div class="productBody"><div class="tagRow"><span class="tag">PBT</span><span class="tag">CHERRY</span></div><div class="priceRow"><span class="price">189,00 zł</span><span class="smallBtn">Dodaj </span></div></div>
 </article>

 <article class="productCard">
 <div class="productHeader"><div><h3>USB‑C Coiled Cable</h3><div class="productMeta">Kabel • Aviator • 2.0 m</div></div><div class="speakerBtn" title="Bez dźwięku">⌁</div></div>
 <div class="productImg"><img src="img/cable2.jpg" alt="Coiled cable" /></div>
 <div class="productBody"><div class="tagRow"><span class="tag">COILED</span><span class="tag">AVIATOR</span></div><div class="priceRow"><span class="price">89,00 zł</span><span class="smallBtn">Dodaj </span></div></div>
 </article>

 <article class="productCard">
 <div class="productHeader"><div><h3>Case 65% Frost</h3><div class="productMeta">Case • 65% • CNC look</div></div><div class="speakerBtn" title="Bez dźwięku">⌁</div></div>
 <div class="productImg"><img src="img/case2.jpg" alt="Keyboard case" /></div>
 <div class="productBody"><div class="tagRow"><span class="tag">CASE</span><span class="tag">65%</span></div><div class="priceRow"><span class="price">249,00 zł</span><span class="smallBtn">Dodaj </span></div></div>
 </article>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card cardHover">
 <h2>Strefa jQuery </h2>
 <p>
 Poniższe elementy służą do pokazania 3 wymaganych animacji jQuery: klik (pulse), hover (karta),
 oraz powiększanie obiektu z każdym kliknięciem.
 </p>
 <div class="jqDemoBox" id="jqGrowBox" title="Klikaj mnie — rosnę z każdym kliknięciem">Klikaj mnie</div>
 <p style="margin-top:10px;">
 </p>
 </section>

 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('switches', ' <section class="card cardHover">
 <h2>Switche</h2>
 <p>
 Poniżej znajdziesz przykładowe produkty w formie dużych kart — to już wygląda jak sklep,
 a w kolejnych labach te dane przeniesiemy do bazy i koszyka.
 </p>
 <div class="subnav" aria-label="Filtry ">
 <span class="pill">Typ: Linear</span>
 <span class="pill">Typ: Tactile</span>
 <span class="pill">Typ: Clicky</span>
 <span class="pill">Sound: Thocky / Clacky / Silent</span>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card">
 <div class="productGrid">
 <!-- 1 -->
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Switch X — Tactile Pro</h3>
 <div class="productMeta">MX • Factory lubed: Yes • Sound: Thocky</div>
 </div>
 <button class="speakerBtn" title="Kliknij, aby odsłuchać" data-sound="audio/tactile_pro.wav" data-playing="0">🔊</button>
 </div>
 <div class="productImg"><img src="img/switch1.jpg" alt="Switch tactile" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Typ</b><span>Tactile</span></div>
 <div class="spec"><b>Actuation</b><span>2.0 mm</span></div>
 <div class="spec"><b>Travel</b><span>4.0 mm</span></div>
 <div class="spec"><b>Force</b><span>67g</span></div>
 <div class="spec"><b>Housing</b><span>Nylon</span></div>
 <div class="spec"><b>Pin</b><span>5‑pin</span></div>
 </div>
 <div class="tagRow"><span class="tag">TACTILE</span><span class="tag">THOCKY</span></div>
 <div class="priceRow"><span class="price">19,90 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <!-- 2 -->
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Switch Y — Linear Smooth</h3>
 <div class="productMeta">MX • Factory lubed: No • Sound: Clacky</div>
 </div>
 <button class="speakerBtn" title="Kliknij, aby odsłuchać" data-sound="audio/linear_red.wav" data-playing="0">🔊</button>
 </div>
 <div class="productImg"><img src="img/switch2.jpg" alt="Switch linear" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Typ</b><span>Linear</span></div>
 <div class="spec"><b>Actuation</b><span>1.9 mm</span></div>
 <div class="spec"><b>Travel</b><span>4.0 mm</span></div>
 <div class="spec"><b>Force</b><span>45g</span></div>
 <div class="spec"><b>Housing</b><span>PC</span></div>
 <div class="spec"><b>Pin</b><span>3‑pin</span></div>
 </div>
 <div class="tagRow"><span class="tag">LINEAR</span><span class="tag">CLACKY</span></div>
 <div class="priceRow"><span class="price">16,90 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <!-- 3 -->
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Switch Z — Clicky Classic</h3>
 <div class="productMeta">MX • Factory lubed: No • Sound: Clicky</div>
 </div>
 <button class="speakerBtn" title="Kliknij, aby odsłuchać" data-sound="audio/holy_panda.wav" data-playing="0">🔊</button>
 </div>
 <div class="productImg"><img src="img/build1.jpg" alt="Switch clicky" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Typ</b><span>Clicky</span></div>
 <div class="spec"><b>Actuation</b><span>2.2 mm</span></div>
 <div class="spec"><b>Travel</b><span>4.0 mm</span></div>
 <div class="spec"><b>Force</b><span>60g</span></div>
 <div class="spec"><b>Housing</b><span>Nylon</span></div>
 <div class="spec"><b>Pin</b><span>5‑pin</span></div>
 </div>
 <div class="tagRow"><span class="tag">CLICKY</span><span class="tag">LOUD</span></div>
 <div class="priceRow"><span class="price">14,90 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <!-- 4 -->
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Switch S — Silent Tactile</h3>
 <div class="productMeta">MX • Factory lubed: Yes • Sound: Silent</div>
 </div>
 <button class="speakerBtn" title="Kliknij, aby odsłuchać" data-sound="audio/clicky_blue.wav" data-playing="0">🔊</button>
 </div>
 <div class="productImg"><img src="img/build2.jpg" alt="Switch silent" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Typ</b><span>Silent tactile</span></div>
 <div class="spec"><b>Actuation</b><span>2.0 mm</span></div>
 <div class="spec"><b>Travel</b><span>3.7 mm</span></div>
 <div class="spec"><b>Force</b><span>62g</span></div>
 <div class="spec"><b>Housing</b><span>POM</span></div>
 <div class="spec"><b>Pin</b><span>5‑pin</span></div>
 </div>
 <div class="tagRow"><span class="tag">SILENT</span><span class="tag">TACTILE</span></div>
 <div class="priceRow"><span class="price">21,90 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>
 </div>

 <p style="margin-top:12px;">
 <b>Info:</b> kliknij ikonę 🔊 — odtwarza próbkę dźwięku switcha (JS + audio).
 </p>
 </section>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('keycaps', ' <section class="card cardHover clearfix">
 <img class="floatImg" src="img/keycaps1.jpg" alt="Keycaps set" />
 <h2>Keycapy</h2>
 <p>
 Keycapy to „skóra” klawiatury — wpływają na wygląd, feeling i brzmienie. Poniżej masz
 przykładowe produkty w formie kart sklepowych (.
 </p>
 <div class="subnav" aria-label="Filtry ">
 <span class="pill">Materiał: PBT</span>
 <span class="pill">Materiał: ABS</span>
 <span class="pill">Profil: Cherry / OEM / SA</span>
 <span class="pill">Legenda: Double‑shot / Dye‑sub</span>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card">
 <div class="productGrid">
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Caps A — PBT Cherry</h3>
 <div class="productMeta">PBT • Cherry • Dye‑sub • 135 klawiszy</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (keycapy)">⌁</div>
 </div>
 <div class="productImg"><img src="img/keycaps1.jpg" alt="Keycaps PBT" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Materiał</b><span>PBT</span></div>
 <div class="spec"><b>Profil</b><span>Cherry</span></div>
 <div class="spec"><b>Grubość</b><span>1.5 mm</span></div>
 <div class="spec"><b>Legenda</b><span>Dye‑sub</span></div>
 <div class="spec"><b>Kompat.</b><span>MX</span></div>
 <div class="spec"><b>Układy</b><span>ANSI/ISO</span></div>
 </div>
 <div class="tagRow"><span class="tag">PBT</span><span class="tag">CHERRY</span></div>
 <div class="priceRow"><span class="price">189,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Caps B — ABS Double‑shot</h3>
 <div class="productMeta">ABS • OEM • Double‑shot • Shine friendly</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (keycapy)">⌁</div>
 </div>
 <div class="productImg"><img src="img/keycaps2.jpg" alt="Keycaps ABS" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Materiał</b><span>ABS</span></div>
 <div class="spec"><b>Profil</b><span>OEM</span></div>
 <div class="spec"><b>Grubość</b><span>1.2 mm</span></div>
 <div class="spec"><b>Legenda</b><span>Double‑shot</span></div>
 <div class="spec"><b>Kompat.</b><span>MX</span></div>
 <div class="spec"><b>Kolor</b><span>Neon</span></div>
 </div>
 <div class="tagRow"><span class="tag">ABS</span><span class="tag">DOUBLE‑SHOT</span></div>
 <div class="priceRow"><span class="price">149,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Caps C — SA Retro</h3>
 <div class="productMeta">PBT • SA • Dye‑sub • Głośniejszy profil</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (keycapy)">⌁</div>
 </div>
 <div class="productImg"><img src="img/desk1.jpg" alt="Keycaps SA" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Materiał</b><span>PBT</span></div>
 <div class="spec"><b>Profil</b><span>SA</span></div>
 <div class="spec"><b>Grubość</b><span>1.5 mm</span></div>
 <div class="spec"><b>Legenda</b><span>Dye‑sub</span></div>
 <div class="spec"><b>Kompat.</b><span>MX</span></div>
 <div class="spec"><b>Układy</b><span>ANSI</span></div>
 </div>
 <div class="tagRow"><span class="tag">RETRO</span><span class="tag">SA</span></div>
 <div class="priceRow"><span class="price">219,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Caps D — Artisan 1u</h3>
 <div class="productMeta">Resin • 1u • R4 • ręczne wykonanie</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (keycapy)">⌁</div>
 </div>
 <div class="productImg"><img src="img/tool1.jpg" alt="Artisan keycap" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Materiał</b><span>Resin</span></div>
 <div class="spec"><b>Rozmiar</b><span>1u</span></div>
 <div class="spec"><b>Profil</b><span>R4</span></div>
 <div class="spec"><b>Kompat.</b><span>MX</span></div>
 <div class="spec"><b>Produkcja</b><span>Handmade</span></div>
 <div class="spec"><b>Limit</b><span>30 szt.</span></div>
 </div>
 <div class="tagRow"><span class="tag">ARTISAN</span><span class="tag">LIMITED</span></div>
 <div class="priceRow"><span class="price">79,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>
 </div>

 <p style="margin-top:12px;">
 <b>Info:</b> PBT zwykle jest odporniejszy na wybłyszczenia, a profil (Cherry/OEM/SA) zmienia
 wysokość i brzmienie.
 </p>
 </section>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('cables', ' <section class="card cardHover clearfix">
 <img class="floatImg" src="img/cable1.jpg" alt="Custom cable" />
 <h2>Kable</h2>
 <p>
 Dobre kable to nie tylko wygląd — liczy się elastyczność, oplot, złącza i długość.
 Poniżej masz przykładowe produkty w układzie sklepowym (.
 </p>
 <div class="subnav" aria-label="Filtry ">
 <span class="pill">USB‑C</span>
 <span class="pill">Aviator</span>
 <span class="pill">Coiled</span>
 <span class="pill">Długość: 1.5 m / 2 m</span>
 </div>
 </section>

 <div style="height:14px;"></div>

 <section class="card">
 <div class="productGrid">
 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Cable A — USB‑C Basic</h3>
 <div class="productMeta">USB‑A → USB‑C • 1.5 m • miękki oplot</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (kable)">⌁</div>
 </div>
 <div class="productImg"><img src="img/cable1.jpg" alt="USB-C cable" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Złącze</b><span>USB‑C</span></div>
 <div class="spec"><b>Długość</b><span>1.5 m</span></div>
 <div class="spec"><b>Oplot</b><span>Nylon</span></div>
 <div class="spec"><b>Kolor</b><span>Neon black</span></div>
 <div class="spec"><b>Ferryt</b><span>Tak</span></div>
 <div class="spec"><b>Gwarancja</b><span>12 mies.</span></div>
 </div>
 <div class="tagRow"><span class="tag">USB‑C</span><span class="tag">BASIC</span></div>
 <div class="priceRow"><span class="price">39,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Cable B — Coiled Neon</h3>
 <div class="productMeta">Coiled • 2.0 m • paracord + techflex</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (kable)">⌁</div>
 </div>
 <div class="productImg"><img src="img/cable2.jpg" alt="Coiled cable" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Złącze</b><span>USB‑C</span></div>
 <div class="spec"><b>Długość</b><span>2.0 m</span></div>
 <div class="spec"><b>Oplot</b><span>Paracord</span></div>
 <div class="spec"><b>Sprężyna</b><span>Tak</span></div>
 <div class="spec"><b>Kolor</b><span>Purple neon</span></div>
 <div class="spec"><b>Gwarancja</b><span>12 mies.</span></div>
 </div>
 <div class="tagRow"><span class="tag">COILED</span><span class="tag">NEON</span></div>
 <div class="priceRow"><span class="price">129,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Cable C — Aviator</h3>
 <div class="productMeta">Aviator GX16 • odpinany • 1.8 m</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (kable)">⌁</div>
 </div>
 <div class="productImg"><img src="img/case1.jpg" alt="Aviator cable" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Złącze</b><span>Aviator</span></div>
 <div class="spec"><b>Długość</b><span>1.8 m</span></div>
 <div class="spec"><b>Oplot</b><span>Techflex</span></div>
 <div class="spec"><b>Wymiana</b><span>Tak</span></div>
 <div class="spec"><b>Kolor</b><span>Black/Green</span></div>
 <div class="spec"><b>Gwarancja</b><span>24 mies.</span></div>
 </div>
 <div class="tagRow"><span class="tag">AVIATOR</span><span class="tag">GX16</span></div>
 <div class="priceRow"><span class="price">169,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>

 <article class="productCard">
 <div class="productHeader">
 <div>
 <h3>Cable D — Desk Mini</h3>
 <div class="productMeta">Krótki • 0.8 m • do setupu biurkowego</div>
 </div>
 <div class="speakerBtn" title="Bez dźwięku (kable)">⌁</div>
 </div>
 <div class="productImg"><img src="img/desk1.jpg" alt="Desk cable" /></div>
 <div class="productBody">
 <div class="specGrid">
 <div class="spec"><b>Złącze</b><span>USB‑C</span></div>
 <div class="spec"><b>Długość</b><span>0.8 m</span></div>
 <div class="spec"><b>Oplot</b><span>Soft</span></div>
 <div class="spec"><b>Kolor</b><span>Smoke</span></div>
 <div class="spec"><b>Ferryt</b><span>Nie</span></div>
 <div class="spec"><b>Gwarancja</b><span>12 mies.</span></div>
 </div>
 <div class="tagRow"><span class="tag">SHORT</span><span class="tag">DESK</span></div>
 <div class="priceRow"><span class="price">29,00 zł</span><span class="smallBtn">Dodaj </span></div>
 </div>
 </article>
 </div>

 <p style="margin-top:12px;">
 <b>Info:</b> Przy coiled kablach zwracaj uwagę na długość po rozciągnięciu i jakość oplotu.
 </p>
 </section>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('guide', ' <section class="card cardHover clearfix">
 <img class="floatImg" src="img/build1.jpg" alt="Keyboard build" />
 <h2>Jak wybrać switche?</h2>
 <p>
 Wybór switchy zależy od tego, co jest dla Ciebie ważne: <b>cisza</b>, <b>szybkość</b>,
 <b>sprężystość</b> lub <b>charakter dźwięku</b>.
 </p>

 <h3>1) Linear</h3>
 <p>Ruch jest płynny, bez wyraźnego „progu”. Dobre do gier i szybkiego pisania.</p>

 <h3>2) Tactile</h3>
 <p>Czujesz lekki „bump” w trakcie nacisku. Dobre do pisania, bez głośnego kliku.</p>

 <h3>3) Clicky</h3>
 <p>Ma wyraźny klik i jest głośniejszy. Charakterystyczne brzmienie — nie dla każdego.</p>

 <p>
 </p>
 </section>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('faq', ' <section class="card cardHover">
 <h2>FAQ</h2>
 <p>Krótko i konkretnie — jeśli nie ma odpowiedzi, napisz do nas przez formularz kontaktowy.</p>

 <div class="faqItem">
 <p class="faqQ">1) Czym są switche mechaniczne?</p>
 <p class="faqA">To przełączniki pod każdym klawiszem. Decydują o odczuciu i brzmieniu klawiatury.</p>
 </div>

 <div class="faqItem">
 <p class="faqQ">2) Jaka jest różnica: linear / tactile / clicky?</p>
 <p class="faqA">Linear — płynny ruch. Tactile — wyczuwalny „bump”. Clicky — bump + głośny klik.</p>
 </div>

 <div class="faqItem">
 <p class="faqQ">3) Czy mogę odsłuchać dźwięk switcha?</p>
 <p class="faqA">Tak — kliknij ikonę 🔊 przy switchu, aby odsłuchać próbkę (wav).</p>
 </div>

 <div class="faqItem">
 <p class="faqQ">4) Czy produkty będą miały ceny brutto (z VAT)?</p>
 <p class="faqA">Tak — w zapisujemy cenę netto i VAT, a koszyk w zlicza wartość brutto.</p>
 </div>

 <div class="faqItem">
 <p class="faqQ">5) Jak działa koszyk?</p>
 <p class="faqA">W koszyk będzie w <b>$_SESSION</b>: dodawanie, usuwanie, zmiana ilości, suma.</p>
 </div>

 <div class="faqItem">
 <p class="faqQ">6) Czy mogę złożyć klawiaturę samodzielnie?</p>
 <p class="faqA">Tak — zacznij od hot-swap PCB i gotowego case’a. Później możesz wejść w lutowanie i tuning.</p>
 </div>
 </section>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('filmy', '<section class="card">
 <h2>Filmy</h2>
 <p>Krótka dawka inspiracji: brzmienie switchy, budowa klawiatury i modowanie.</p>

 <h3>1) Materiał wideo</h3>
 <iframe width="100%" height="360" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Video 1" frameborder="0" allowfullscreen></iframe>

 <h3>2) Materiał wideo</h3>
 <iframe width="100%" height="360" src="https://www.youtube.com/embed/9bZkp7q19f0" title="Video 2" frameborder="0" allowfullscreen></iframe>

 <h3>3) Materiał wideo</h3>
 <iframe width="100%" height="360" src="https://www.youtube.com/embed/kJQP7kiw5Fk" title="Video 3" frameborder="0" allowfullscreen></iframe>
</section>

 <h3>2) Materiał wideo</h3>
 <iframe width="100%" height="360" src="https://www.youtube.com/embed/9bZkp7q19f0" title="Video 2" frameborder="0" allowfullscreen></iframe>

 <h3>3) Materiał wideo</h3>
 <iframe width="100%" height="360" src="https://www.youtube.com/embed/kJQP7kiw5Fk" title="Video 3" frameborder="0" allowfullscreen></iframe>
</section>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('contact', ' <div class="grid2">
 <section class="card cardHover">
 <h2>Kontakt</h2>
 <p>
 Formularz jest spięty z klientem poczty przez <b>mailto:</b>.
 Wpisz swój email docelowy w atrybucie <u>action</u>.
 </p>

 <form method="post" action="mailto:twoj_email@example.com" enctype="text/plain">
 <div class="formGrid">
 <div>
 <label for="name"><b>Imię</b></label><br />
 <input class="input" id="name" name="Imie" type="text" placeholder="Np. Jan" required />
 </div>

 <div>
 <label for="email"><b>E-mail</b></label><br />
 <input class="input" id="email" name="Email" type="email" placeholder="np. jan@mail.com" required />
 </div>
 </div>

 <div style="margin-top:12px;">
 <label for="topic"><b>Temat</b></label><br />
 <input class="input" id="topic" name="Temat" type="text" placeholder="Np. Pytanie o switche" />
 </div>

 <div style="margin-top:12px;">
 <label for="msg"><b>Wiadomość</b></label><br />
 <textarea id="msg" name="Wiadomosc" placeholder="Napisz wiadomość..."></textarea>
 </div>

 <div style="margin-top:12px;">
 <button class="btn" type="submit">Wyślij</button>
 </div>
 </form>
 </section>

 <aside class="card cardHover clearfix">
 <img class="floatImg" src="img/cable1.jpg" alt="Custom cable" />
 <h3>Godziny kontaktu</h3>
 <ul class="ul">
 <li>Pon–Pt: 10:00–18:00</li>
 <li>Sob: 10:00–14:00</li>
 <li>Nd: nieczynne</li>
 </ul>
 <p>
 </p>
 </aside>
 </div>
 </td>
', 1);
INSERT INTO page_list (page_title, page_content, status) VALUES ('about', ' <div class="grid2">
 <section class="card cardHover clearfix">
 <img class="floatImg" src="img/desk1.jpg" alt="Desk setup" />
 <h2>O ClickClick</h2>
 <p>
 ClickClick to przykładowy sklep z częściami do klawiatur mechanicznych: bazy, switche,
 keycapy, kable, stabilizatory i akcesoria.
 </p>
 <p>
 Strona będzie rozwijana etapami: JavaScript, jQuery, PHP, baza danych i panel CMS,
 a na końcu — koszyk internetowy.
 </p>

 <h3>Dlaczego mechaniki?</h3>
 <p>
 Bo pozwalają dopasować <b>brzmienie</b>, <b>feel</b> i <b>wygląd</b> pod siebie.
 To trochę jak składanie PC — tylko w miniaturze 😉
 </p>
 </section>

 <aside class="card cardHover">
 <img src="img/build2.jpg" alt="Keyboard build" style="width:100%; border-radius:18px; border:1px solid rgba(255,255,255,.14);" />
 <h3 style="margin-top:12px;">Kontakt</h3>
 <p>
 Masz pytania? Przejdź do zakładki <a href="contact"><b>Kontakt</b></a>.
 </p>
 </aside>
 </div>
 </td>
', 1);