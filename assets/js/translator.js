/**
 * NexVest — 8-Language Translator
 * Languages: EN, FR, ES, DE, ZH, AR, PT, RU
 */

const TRANSLATIONS = {
  en: {
    // Auth
    'Sign In': 'Sign In', 'Welcome Back': 'Welcome Back',
    'Sign in to your account to continue': 'Sign in to your account to continue',
    'Email Address': 'Email Address', 'Password': 'Password',
    'Forgot Password?': 'Forgot Password?', 'Create Account': 'Create Account',
    'Already have an account?': 'Already have an account?',
    'First Name': 'First Name', 'Last Name': 'Last Name',
    'Phone Number': 'Phone Number', 'Country of Residence': 'Country of Residence',
    'Confirm Password': 'Confirm Password', 'Verify Email': 'Verify Email',
    'Resend Code': 'Resend Code', 'Back to Sign In': 'Back to Sign In',
    'Reset Password': 'Reset Password', 'Send Reset Link': 'Send Reset Link',
    // Dashboard
    'Dashboard': 'Dashboard', 'Overview': 'Overview',
    'Investments': 'Investments', 'My Portfolio': 'My Portfolio',
    'Wallet': 'Wallet', 'Transactions': 'Transactions',
    'Notifications': 'Notifications', 'Certificates': 'Certificates',
    'Referrals': 'Referrals', 'Calculator': 'Calculator',
    'Support': 'Support', 'My Profile': 'My Profile',
    'Sign Out': 'Sign Out', 'Balance': 'Balance',
    // Wallet
    'Deposit': 'Deposit', 'Withdraw': 'Withdraw',
    'Total Deposited': 'Total Deposited', 'Total Returns': 'Total Returns',
    'Total Withdrawn': 'Total Withdrawn', 'Available Balance': 'Available Balance',
    // Investments
    'Invest Now': 'Invest Now', 'View Details': 'View Details',
    'Annual ROI': 'Annual ROI', 'Duration': 'Duration',
    'Min. Investment': 'Min. Investment', 'Real Estate': 'Real Estate',
    'Index Fund': 'Index Fund', 'Active': 'Active', 'Funded': 'Funded',
    // Common
    'Submit': 'Submit', 'Cancel': 'Cancel', 'Save': 'Save', 'Close': 'Close',
    'Back': 'Back', 'Continue': 'Continue', 'Confirm': 'Confirm',
    'Loading...': 'Loading...', 'Success': 'Success', 'Error': 'Error',
  },
  fr: {
    'Sign In': 'Se connecter', 'Welcome Back': 'Bon retour',
    'Sign in to your account to continue': 'Connectez-vous pour continuer',
    'Email Address': 'Adresse e-mail', 'Password': 'Mot de passe',
    'Forgot Password?': 'Mot de passe oublié ?', 'Create Account': 'Créer un compte',
    'Already have an account?': 'Vous avez déjà un compte ?',
    'First Name': 'Prénom', 'Last Name': 'Nom de famille',
    'Phone Number': 'Numéro de téléphone', 'Country of Residence': 'Pays de résidence',
    'Confirm Password': 'Confirmer le mot de passe', 'Verify Email': 'Vérifier l\'e-mail',
    'Resend Code': 'Renvoyer le code', 'Back to Sign In': 'Retour à la connexion',
    'Reset Password': 'Réinitialiser le mot de passe', 'Send Reset Link': 'Envoyer le lien',
    'Dashboard': 'Tableau de bord', 'Overview': 'Aperçu',
    'Investments': 'Investissements', 'My Portfolio': 'Mon portefeuille',
    'Wallet': 'Portefeuille', 'Transactions': 'Transactions',
    'Notifications': 'Notifications', 'Certificates': 'Certificats',
    'Referrals': 'Parrainages', 'Calculator': 'Calculateur',
    'Support': 'Support', 'My Profile': 'Mon profil',
    'Sign Out': 'Déconnexion', 'Balance': 'Solde',
    'Deposit': 'Dépôt', 'Withdraw': 'Retrait',
    'Total Deposited': 'Total déposé', 'Total Returns': 'Total des rendements',
    'Total Withdrawn': 'Total retiré', 'Available Balance': 'Solde disponible',
    'Invest Now': 'Investir maintenant', 'View Details': 'Voir les détails',
    'Annual ROI': 'ROI annuel', 'Duration': 'Durée',
    'Min. Investment': 'Invest. min.', 'Real Estate': 'Immobilier',
    'Index Fund': 'Fonds indiciel', 'Active': 'Actif', 'Funded': 'Financé',
    'Submit': 'Soumettre', 'Cancel': 'Annuler', 'Save': 'Enregistrer',
    'Close': 'Fermer', 'Back': 'Retour', 'Continue': 'Continuer',
    'Confirm': 'Confirmer', 'Loading...': 'Chargement...', 'Success': 'Succès', 'Error': 'Erreur',
  },
  es: {
    'Sign In': 'Iniciar sesión', 'Welcome Back': 'Bienvenido de nuevo',
    'Sign in to your account to continue': 'Inicia sesión para continuar',
    'Email Address': 'Correo electrónico', 'Password': 'Contraseña',
    'Forgot Password?': '¿Olvidaste tu contraseña?', 'Create Account': 'Crear cuenta',
    'Already have an account?': '¿Ya tienes una cuenta?',
    'First Name': 'Nombre', 'Last Name': 'Apellido',
    'Phone Number': 'Número de teléfono', 'Country of Residence': 'País de residencia',
    'Confirm Password': 'Confirmar contraseña', 'Verify Email': 'Verificar correo',
    'Resend Code': 'Reenviar código', 'Back to Sign In': 'Volver al inicio de sesión',
    'Reset Password': 'Restablecer contraseña', 'Send Reset Link': 'Enviar enlace',
    'Dashboard': 'Panel', 'Overview': 'Resumen',
    'Investments': 'Inversiones', 'My Portfolio': 'Mi cartera',
    'Wallet': 'Billetera', 'Transactions': 'Transacciones',
    'Notifications': 'Notificaciones', 'Certificates': 'Certificados',
    'Referrals': 'Referencias', 'Calculator': 'Calculadora',
    'Support': 'Soporte', 'My Profile': 'Mi perfil',
    'Sign Out': 'Cerrar sesión', 'Balance': 'Saldo',
    'Deposit': 'Depósito', 'Withdraw': 'Retiro',
    'Total Deposited': 'Total depositado', 'Total Returns': 'Rendimientos totales',
    'Available Balance': 'Saldo disponible',
    'Invest Now': 'Invertir ahora', 'View Details': 'Ver detalles',
    'Annual ROI': 'ROI anual', 'Duration': 'Duración',
    'Min. Investment': 'Inv. mín.', 'Real Estate': 'Bienes raíces',
    'Index Fund': 'Fondo índice', 'Active': 'Activo', 'Funded': 'Financiado',
    'Submit': 'Enviar', 'Cancel': 'Cancelar', 'Save': 'Guardar',
    'Close': 'Cerrar', 'Back': 'Atrás', 'Continue': 'Continuar',
    'Confirm': 'Confirmar', 'Loading...': 'Cargando...', 'Error': 'Error',
  },
  de: {
    'Sign In': 'Anmelden', 'Welcome Back': 'Willkommen zurück',
    'Sign in to your account to continue': 'Melden Sie sich an, um fortzufahren',
    'Email Address': 'E-Mail-Adresse', 'Password': 'Passwort',
    'Forgot Password?': 'Passwort vergessen?', 'Create Account': 'Konto erstellen',
    'First Name': 'Vorname', 'Last Name': 'Nachname',
    'Country of Residence': 'Wohnsitzland', 'Confirm Password': 'Passwort bestätigen',
    'Dashboard': 'Übersicht', 'Investments': 'Investitionen',
    'My Portfolio': 'Mein Portfolio', 'Wallet': 'Wallet',
    'Transactions': 'Transaktionen', 'Notifications': 'Benachrichtigungen',
    'Sign Out': 'Abmelden', 'Balance': 'Kontostand',
    'Deposit': 'Einzahlung', 'Withdraw': 'Auszahlung',
    'Invest Now': 'Jetzt investieren', 'Annual ROI': 'Jährlicher ROI',
    'Real Estate': 'Immobilien', 'Index Fund': 'Indexfonds',
    'Submit': 'Einreichen', 'Cancel': 'Abbrechen', 'Save': 'Speichern',
    'Close': 'Schließen', 'Back': 'Zurück', 'Confirm': 'Bestätigen',
  },
  zh: {
    'Sign In': '登录', 'Welcome Back': '欢迎回来',
    'Sign in to your account to continue': '登录您的账户继续',
    'Email Address': '电子邮件', 'Password': '密码',
    'Forgot Password?': '忘记密码？', 'Create Account': '创建账户',
    'First Name': '名字', 'Last Name': '姓氏',
    'Country of Residence': '居住国', 'Confirm Password': '确认密码',
    'Dashboard': '仪表板', 'Investments': '投资',
    'My Portfolio': '我的投资组合', 'Wallet': '钱包',
    'Transactions': '交易', 'Notifications': '通知',
    'Sign Out': '退出登录', 'Balance': '余额',
    'Deposit': '存款', 'Withdraw': '提款',
    'Invest Now': '立即投资', 'Annual ROI': '年化收益率',
    'Real Estate': '房地产', 'Index Fund': '指数基金',
    'Submit': '提交', 'Cancel': '取消', 'Save': '保存',
    'Close': '关闭', 'Back': '返回', 'Confirm': '确认',
  },
  ar: {
    'Sign In': 'تسجيل الدخول', 'Welcome Back': 'مرحباً بعودتك',
    'Sign in to your account to continue': 'سجّل دخولك للمتابعة',
    'Email Address': 'البريد الإلكتروني', 'Password': 'كلمة المرور',
    'Forgot Password?': 'نسيت كلمة المرور؟', 'Create Account': 'إنشاء حساب',
    'First Name': 'الاسم الأول', 'Last Name': 'اسم العائلة',
    'Country of Residence': 'بلد الإقامة', 'Confirm Password': 'تأكيد كلمة المرور',
    'Dashboard': 'لوحة التحكم', 'Investments': 'الاستثمارات',
    'My Portfolio': 'محفظتي', 'Wallet': 'المحفظة',
    'Transactions': 'المعاملات', 'Notifications': 'الإشعارات',
    'Sign Out': 'تسجيل الخروج', 'Balance': 'الرصيد',
    'Deposit': 'إيداع', 'Withdraw': 'سحب',
    'Invest Now': 'استثمر الآن', 'Annual ROI': 'العائد السنوي',
    'Real Estate': 'العقارات', 'Index Fund': 'صندوق المؤشرات',
    'Submit': 'إرسال', 'Cancel': 'إلغاء', 'Save': 'حفظ',
    'Close': 'إغلاق', 'Back': 'رجوع', 'Confirm': 'تأكيد',
  },
  pt: {
    'Sign In': 'Entrar', 'Welcome Back': 'Bem-vindo de volta',
    'Email Address': 'Endereço de email', 'Password': 'Senha',
    'Forgot Password?': 'Esqueceu a senha?', 'Create Account': 'Criar conta',
    'First Name': 'Nome', 'Last Name': 'Sobrenome',
    'Country of Residence': 'País de residência', 'Confirm Password': 'Confirmar senha',
    'Dashboard': 'Painel', 'Investments': 'Investimentos',
    'My Portfolio': 'Minha carteira', 'Wallet': 'Carteira',
    'Transactions': 'Transações', 'Sign Out': 'Sair', 'Balance': 'Saldo',
    'Deposit': 'Depósito', 'Withdraw': 'Saque',
    'Invest Now': 'Investir agora', 'Annual ROI': 'ROI anual',
    'Real Estate': 'Imóveis', 'Index Fund': 'Fundo de índice',
    'Submit': 'Enviar', 'Cancel': 'Cancelar', 'Save': 'Salvar',
  },
  ru: {
    'Sign In': 'Войти', 'Welcome Back': 'Добро пожаловать',
    'Email Address': 'Электронная почта', 'Password': 'Пароль',
    'Forgot Password?': 'Забыли пароль?', 'Create Account': 'Создать аккаунт',
    'First Name': 'Имя', 'Last Name': 'Фамилия',
    'Country of Residence': 'Страна проживания', 'Confirm Password': 'Подтвердите пароль',
    'Dashboard': 'Панель управления', 'Investments': 'Инвестиции',
    'My Portfolio': 'Мой портфель', 'Wallet': 'Кошелёк',
    'Transactions': 'Транзакции', 'Sign Out': 'Выйти', 'Balance': 'Баланс',
    'Deposit': 'Депозит', 'Withdraw': 'Вывод',
    'Invest Now': 'Инвестировать', 'Annual ROI': 'Годовая доходность',
    'Real Estate': 'Недвижимость', 'Index Fund': 'Индексный фонд',
    'Submit': 'Отправить', 'Cancel': 'Отмена', 'Save': 'Сохранить',
  }
};

// ── Translator Class ─────────────────────────────────────────
const Translator = {
  currentLang: localStorage.getItem('nexvest_lang') || 'en',
  rtlLangs: ['ar'],

  init() {
    this.applyLang(this.currentLang);
    this.renderSwitcher();
  },

  t(key) {
    return TRANSLATIONS[this.currentLang]?.[key] || TRANSLATIONS['en'][key] || key;
  },

  applyLang(lang) {
    this.currentLang = lang;
    localStorage.setItem('nexvest_lang', lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = this.rtlLangs.includes(lang) ? 'rtl' : 'ltr';

    // Add RTL styles if needed
    const rtlStyle = document.getElementById('rtl-style');
    if (this.rtlLangs.includes(lang)) {
      if (!rtlStyle) {
        const s = document.createElement('style');
        s.id = 'rtl-style';
        s.textContent = '.sidebar{left:auto;right:0}.main{margin-left:0;margin-right:var(--sidebar)}.sb-item::before{left:auto;right:0;border-radius:2px 0 0 2px}';
        document.head.appendChild(s);
      }
    } else {
      rtlStyle?.remove();
    }

    // Translate all elements with data-i18n attribute
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key = el.getAttribute('data-i18n');
      const translation = this.t(key);
      if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        el.placeholder = translation;
      } else {
        el.textContent = translation;
      }
    });

    // Update switcher active state
    document.querySelectorAll('.lang-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });
  },

  renderSwitcher() {
    const langs = [
      { code: 'en', label: 'EN', name: 'English' },
      { code: 'fr', label: 'FR', name: 'Français' },
      { code: 'es', label: 'ES', name: 'Español' },
      { code: 'de', label: 'DE', name: 'Deutsch' },
      { code: 'zh', label: 'ZH', name: '中文' },
      { code: 'ar', label: 'AR', name: 'العربية' },
      { code: 'pt', label: 'PT', name: 'Português' },
      { code: 'ru', label: 'RU', name: 'Русский' },
    ];

    const html = langs.map(l =>
      `<button class="lang-btn${l.code === this.currentLang ? ' active' : ''}"
        data-lang="${l.code}" title="${l.name}"
        onclick="Translator.applyLang('${l.code}')">${l.label}</button>`
    ).join('');

    // Render in sidebar (desktop) and topbar (mobile)
    // Render in all possible containers
    ['lang-switcher', 'lang-switcher-auth'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html;
    });
    const sidebar = document.getElementById('lang-switcher');

    const topbar = document.getElementById('lang-switcher-top');
    if (topbar) {
      topbar.innerHTML = html;
      // Style topbar buttons differently (smaller, on light bg)
      topbar.querySelectorAll('.lang-btn').forEach(btn => {
        btn.style.cssText = 'padding:2px 5px;font-size:9px;border-color:var(--border);color:var(--text3);background:transparent;border:1px solid var(--border);border-radius:3px;cursor:pointer;font-family:inherit;font-weight:600';
        if (btn.dataset.lang === this.currentLang) {
          btn.style.background = 'var(--accent)';
          btn.style.color = '#fff';
          btn.style.borderColor = 'var(--accent)';
        }
        btn.addEventListener('click', () => Translator.applyLang(btn.dataset.lang));
      });
      // Hide on desktop (sidebar has it), show on mobile
      topbar.style.cssText = 'display:none';
      const style = document.getElementById('lang-top-style') || document.createElement('style');
      style.id = 'lang-top-style';
      style.textContent = '@media(max-width:768px){#lang-switcher-top{display:flex!important;gap:2px}}';
      document.head.appendChild(style);
    }
  }
};

// Style for language switcher
const langStyle = document.createElement('style');
langStyle.textContent = `
  #lang-switcher{display:flex;gap:3px;flex-wrap:wrap}
  .lang-btn{padding:3px 7px;border:1px solid rgba(255,255,255,.2);background:transparent;color:rgba(255,255,255,.5);font-size:10px;font-weight:700;border-radius:3px;cursor:pointer;font-family:inherit;transition:all .15s}
  .lang-btn:hover{color:#fff;border-color:rgba(255,255,255,.5)}
  .lang-btn.active{background:var(--accent,#1B6CA8);color:#fff;border-color:var(--accent,#1B6CA8)}
`;
document.head.appendChild(langStyle);

document.addEventListener('DOMContentLoaded', () => Translator.init());
