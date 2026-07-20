<?php
$countries = ['Afghanistan','Albania','Algeria','Andorra','Angola','Antigua and Barbuda','Argentina','Armenia','Australia','Austria','Azerbaijan','Bahamas','Bahrain','Bangladesh','Barbados','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bosnia and Herzegovina','Botswana','Brazil','Brunei','Bulgaria','Burkina Faso','Burundi','Cabo Verde','Cambodia','Cameroon','Canada','Central African Republic','Chad','Chile','China','Colombia','Comoros','Congo (Congo-Brazzaville)','Costa Rica','Croatia','Cuba','Cyprus','Czech Republic','Democratic Republic of the Congo','Denmark','Djibouti','Dominica','Dominican Republic','Ecuador','Egypt','El Salvador','Equatorial Guinea','Eritrea','Estonia','Eswatini','Ethiopia','Fiji','Finland','France','Gabon','Gambia','Georgia','Germany','Ghana','Greece','Grenada','Guatemala','Guinea','Guinea-Bissau','Guyana','Haiti','Honduras','Hungary','Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Italy','Ivory Coast','Jamaica','Japan','Jordan','Kazakhstan','Kenya','Kiribati','Kuwait','Kyrgyzstan','Laos','Latvia','Lebanon','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Luxembourg','Madagascar','Malawi','Malaysia','Maldives','Mali','Malta','Marshall Islands','Mauritania','Mauritius','Mexico','Micronesia','Moldova','Monaco','Mongolia','Montenegro','Morocco','Mozambique','Myanmar','Namibia','Nauru','Nepal','Netherlands','New Zealand','Nicaragua','Niger','Nigeria','North Korea','North Macedonia','Norway','Oman','Pakistan','Palau','Palestine','Panama','Papua New Guinea','Paraguay','Peru','Philippines','Poland','Portugal','Qatar','Romania','Russia','Rwanda','Saint Kitts and Nevis','Saint Lucia','Saint Vincent and the Grenadines','Samoa','San Marino','Sao Tome and Principe','Saudi Arabia','Senegal','Serbia','Seychelles','Sierra Leone','Singapore','Slovakia','Slovenia','Solomon Islands','Somalia','South Africa','South Korea','South Sudan','Spain','Sri Lanka','Sudan','Suriname','Sweden','Switzerland','Syria','Taiwan','Tajikistan','Tanzania','Thailand','Timor-Leste','Togo','Tonga','Trinidad and Tobago','Tunisia','Turkey','Turkmenistan','Tuvalu','Uganda','Ukraine','United Arab Emirates','United Kingdom','United States','Uruguay','Uzbekistan','Vanuatu','Vatican City','Venezuela','Vietnam','Yemen','Zambia','Zimbabwe'];
$refCode = htmlspecialchars($_GET['ref'] ?? '');
?>
<p class="card-eyebrow">New investor</p>
<h2 class="card-title">Create your account</h2>
<p class="card-sub">Start building your portfolio in a few minutes.</p>

<?php if ($refCode): ?>
<div class="alert-banner ok" style="margin-bottom:1.25rem">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
  <span>You were referred by a friend &mdash; code <strong><?= $refCode ?></strong> will be applied automatically.</span>
</div>
<?php endif; ?>

<div id="reg-alert"></div>

<form id="reg-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
  <?php if ($refCode): ?><input type="hidden" name="referral_code" value="<?= $refCode ?>"/><?php endif; ?>

  <div class="frow">
    <div class="field">
      <label for="first_name" class="flabel">First name</label>
      <input id="first_name" class="finput no-icon" type="text" name="first_name" placeholder="John" required autocomplete="given-name"/>
    </div>
    <div class="field">
      <label for="last_name" class="flabel">Last name</label>
      <input id="last_name" class="finput no-icon" type="text" name="last_name" placeholder="Smith" required autocomplete="family-name"/>
    </div>
  </div>

  <div class="field">
    <label for="email" class="flabel">Email address</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
      <input id="email" class="finput" type="email" name="email" placeholder="you@example.com" required autocomplete="email"/>
    </div>
  </div>

  <div class="frow">
    <div class="field">
      <label for="phone" class="flabel">Phone <span style="color:var(--mist-400);font-weight:400">(optional)</span></label>
      <input id="phone" class="finput no-icon" type="tel" name="phone" placeholder="+1 555 000 0000" autocomplete="tel"/>
    </div>
    <div class="field">
      <label for="country" class="flabel">Country</label>
      <select id="country" class="fsel" name="country" required>
        <option value="">Select…</option>
        <?php foreach ($countries as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option><?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="field">
    <label for="password" class="flabel">Password</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input id="password" class="finput pwd" type="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password"/>
      <button type="button" class="pwd-toggle" data-toggle-password="password" aria-label="Show password">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
    <div class="pwd-reqs" id="pwd-reqs">
      <span class="pwd-req" data-rule="len"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>8+ characters</span>
      <span class="pwd-req" data-rule="case"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Upper &amp; lowercase</span>
      <span class="pwd-req" data-rule="num"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>A number</span>
    </div>
  </div>

  <div class="field">
    <label for="confirm_password" class="flabel">Confirm password</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input id="confirm_password" class="finput pwd" type="password" name="confirm_password" placeholder="••••••••" required autocomplete="new-password"/>
      <button type="button" class="pwd-toggle" data-toggle-password="confirm_password" aria-label="Show password">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
    <div id="pwd-match-hint" class="fhelp"></div>
  </div>

  <div class="fcb">
    <input type="checkbox" id="terms" name="terms" required/>
    <label for="terms">I agree to the <a href="/terms" target="_blank" rel="noopener">Terms of Service</a> and <a href="/privacy" target="_blank" rel="noopener">Privacy Policy</a></label>
  </div>

  <button type="submit" class="btn-primary" id="reg-btn">
    <span id="reg-btn-label">Create account</span>
  </button>
</form>

<div class="trust-row" style="margin-top:1.5rem">
  <span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    256-bit encryption
  </span>
  <span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    KYC / AML compliant
  </span>
</div>

<p class="form-footer">Already have an account? <a href="/login" class="flink">Sign in</a></p>

<script>
(function () {
  var pwd = document.getElementById('password');
  var confirm = document.getElementById('confirm_password');
  var hint = document.getElementById('pwd-match-hint');
  var formCard = document.querySelector('.form-card');

  function checkReqs() {
    var v = pwd.value;
    var rules = {
      len:  v.length >= 8,
      case: /[a-z]/.test(v) && /[A-Z]/.test(v),
      num:  /[0-9]/.test(v),
    };
    Object.keys(rules).forEach(function (key) {
      var el = document.querySelector('.pwd-req[data-rule="' + key + '"]');
      el.classList.toggle('ok', rules[key]);
    });
  }
  pwd.addEventListener('input', checkReqs);

  function checkMatch() {
    if (!confirm.value) { hint.textContent = ''; hint.style.color = ''; return; }
    if (confirm.value === pwd.value) {
      hint.textContent = 'Passwords match';
      hint.style.color = 'var(--em-600)';
    } else {
      hint.textContent = 'Passwords do not match';
      hint.style.color = 'var(--red-700)';
    }
  }
  confirm.addEventListener('input', checkMatch);
  pwd.addEventListener('input', checkMatch);

  function showAlert(type, message) {
    var icons = {
      err: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
    };
    document.getElementById('reg-alert').innerHTML =
      '<div class="alert-banner ' + type + '">' + icons[type] + '<span>' + message + '</span></div>';
  }

  document.getElementById('reg-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('reg-btn');
    var label = document.getElementById('reg-btn-label');
    document.getElementById('reg-alert').innerHTML = '';
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Creating account…';

    try {
      var fd = new FormData(e.target);
      var data = await post('/register', fd, true);
      if (data.success) {
        label.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Success';
        window.location.href = data.redirect;
      } else {
        var msg = data.errors ? data.errors.join('<br>') : (data.error || 'Registration failed. Please try again.');
        showAlert('err', msg);
        btn.disabled = false;
        label.textContent = 'Create account';
        formCard.classList.add('shake');
        setTimeout(function () { formCard.classList.remove('shake'); }, 400);
      }
    } catch (err) {
      showAlert('err', 'An unexpected error occurred. Please refresh and try again.');
      btn.disabled = false;
      label.textContent = 'Create account';
    }
  });
})();
</script>
