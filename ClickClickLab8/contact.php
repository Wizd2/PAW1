<?php
// ClickClick — v1.7 (Lab8)
// contact.php: PokazKontakt(), WyslijMailKontakt(), PrzypomnijHaslo()

function _cc_escape($s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function _cc_post(string $key): string {
  return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

function _cc_is_email(string $email): bool {
  return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 1) PokazKontakt()
function PokazKontakt(): string {
  global $admin_email;

  $info = '';
  if (isset($_GET['sent']) && $_GET['sent'] === '1') {
    $info = '<div class="card" style="border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.08);">
              <b>Wiadomosc zostala przetworzona.</b><br />
              Jesli serwer ma skonfigurowana poczte, e-mail zostanie wyslany.
            </div><div style="height:12px"></div>';
  }

  $safeEmail = _cc_escape((string)$admin_email);

  return $info . '
  <div class="grid2">
    <section class="card">
      <h2>Kontakt</h2>
      <p>Formularz wysyla wiadomosc przez PHP (Lab8). Odbiorca: <b>' . $safeEmail . '</b>.</p>

      <form method="post" action="index.php?idp=contact&action=send">
        <div class="formGrid">
          <div>
            <label for="name"><b>Imie</b></label><br />
            <input class="input" id="name" name="name" type="text" placeholder="Np. Jan" required />
          </div>
          <div>
            <label for="email"><b>E-mail</b></label><br />
            <input class="input" id="email" name="email" type="email" placeholder="np. jan@mail.com" required />
          </div>
        </div>

        <div style="margin-top:12px;">
          <label for="subject"><b>Temat</b></label><br />
          <input class="input" id="subject" name="subject" type="text" placeholder="Np. Pytanie o switche" required />
        </div>

        <div style="margin-top:12px;">
          <label for="message"><b>Wiadomosc</b></label><br />
          <textarea id="message" name="message" placeholder="Napisz wiadomosc..." required></textarea>
        </div>

        <div style="margin-top:12px;">
          <button class="btn" type="submit">Wyślij</button>
        </div>
      </form>
    </section>

    <aside class="card">
      <h3>Przypomnij haslo do panelu admina</h3>
      <p>Uproszczona funkcja (Lab8) - wysyla dane logowania e-mailem.</p>

      <form method="post" action="index.php?idp=contact&action=remind">
        <label for="remind_email"><b>E-mail (odbiorca)</b></label><br />
        <input class="input" id="remind_email" name="remind_email" type="email" value="' . $safeEmail . '" required />
        <div style="margin-top:12px;">
          <button class="btn" type="submit">Wyślij przypomnienie</button>
        </div>
      </form>

      <div style="height:12px"></div>
      <p style="font-size:12px;">Tip: w realnych systemach hasel sie nie wysyla wprost - robi sie reset linkiem. Tutaj to tylko cwiczenie.</p>
    </aside>
  </div>';
}

// 2) WyslijMailKontakt()
function WyslijMailKontakt(): string {
  global $admin_email;

  $name = _cc_post('name');
  $email = _cc_post('email');
  $subject = _cc_post('subject');
  $message = _cc_post('message');

  $errors = [];
  if ($name === '') $errors[] = 'Brak imienia.';
  if (!$email || !_cc_is_email($email)) $errors[] = 'Niepoprawny e-mail.';
  if ($subject === '') $errors[] = 'Brak tematu.';
  if ($message === '') $errors[] = 'Brak wiadomosci.';

  if (!empty($errors)) {
    $out = '<div class="card" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08);">
              <b>Nie mozna wyslac wiadomosci:</b><br />
              <ul class="ul">';
    foreach ($errors as $e) {
      $out .= '<li>' . _cc_escape($e) . '</li>';
    }
    $out .= '</ul></div><div style="height:12px"></div>';
    $out .= PokazKontakt();
    return $out;
  }

  $to = (string)$admin_email;
  $safeSubject = 'ClickClick kontakt: ' . $subject;

  $body = "Nowa wiadomosc z formularza ClickClick\n";
  $body .= "Imie: {$name}\n";
  $body .= "E-mail: {$email}\n";
  $body .= "Temat: {$subject}\n\n";
  $body .= "Wiadomosc:\n{$message}\n";

  $headers = [];
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/plain; charset=UTF-8';
  $headers[] = 'From: ClickClick <no-reply@clickclick.local>';
  $headers[] = 'Reply-To: ' . $email;

  $sent = @mail($to, $safeSubject, $body, implode("\r\n", $headers));

  if ($sent) {
    header('Location: index.php?idp=contact&sent=1');
    exit;
  }

  // W wielu XAMPP na Windows mail() moze byc nieaktywny - pokazujemy czytelny komunikat
  $out = '<div class="card" style="border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.08);">
            <b>Formularz przetworzony, ale funkcja mail() mogla nie wyslac wiadomosci.</b><br />
            To czeste w lokalnym XAMPP. Kod jest poprawny na potrzeby Lab8.
          </div><div style="height:12px"></div>';
  $out .= PokazKontakt();
  return $out;
}

// 3) PrzypomnijHaslo()
function PrzypomnijHaslo(): string {
  global $admin_email, $login, $pass;

  $to = _cc_post('remind_email');
  if (!$to || !_cc_is_email($to)) {
    return '<div class="card" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08);">
              <b>Niepoprawny e-mail odbiorcy.</b>
            </div><div style="height:12px"></div>' . PokazKontakt();
  }

  $subject = 'ClickClick - przypomnienie hasla (Lab8)';
  $body = "Przypomnienie danych logowania do panelu admina ClickClick\n\n";
  $body .= "Login: {$login}\n";
  $body .= "Haslo: {$pass}\n\n";
  $body .= "(Uwaga: to tylko cwiczenie - w prawdziwych systemach hasel sie nie wysyla.)\n";

  $headers = [];
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/plain; charset=UTF-8';
  $headers[] = 'From: ClickClick <no-reply@clickclick.local>';

  $sent = @mail($to, $subject, $body, implode("\r\n", $headers));

  if ($sent) {
    $ok = '<div class="card" style="border-color: rgba(34,197,94,.35); background: rgba(34,197,94,.08);">
            <b>Wyslano przypomnienie.</b><br />
            Sprawdz skrzynke: ' . _cc_escape($to) . '
          </div><div style="height:12px"></div>';
    return $ok . PokazKontakt();
  }

  $warn = '<div class="card" style="border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.08);">
            <b>Nie udalo sie wyslac maila funkcja mail().</b><br />
            W XAMPP lokalnie to bywa normalne. Kod jest na potrzeby Lab8.
          </div><div style="height:12px"></div>';
  return $warn . PokazKontakt();
}
