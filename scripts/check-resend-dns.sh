#!/usr/bin/env bash
# ============================================================
# Vérification des enregistrements DNS Resend pour « Le Pharaon »
#
# Usage :
#   bash scripts/check-resend-dns.sh lepharaon.com
#   bash scripts/check-resend-dns.sh mail.lepharaon.com
#
# Vérifie les 3 enregistrements attendus par Resend (+ DMARC recommandé) :
#   MX  send           -> feedback-smtp.us-east-1.amazonses.com (prio 10)
#   TXT send           -> v=spf1 include:amazonses.com ~all
#   TXT resend._domainkey -> clé publique DKIM (valeur générée par Resend)
#   TXT _dmarc         -> v=DMARC1; ... (recommandé)
#
# Les VALEURS EXACTES de MX/SPF/DKIM sont affichées dans le dashboard
# Resend (Domains) — ce script vérifie que les enregistrements répondent.
# ============================================================

set -u

DOMAIN="${1:-}"
if [ -z "$DOMAIN" ]; then
  echo "Usage: bash scripts/check-resend-dns.sh <votre-domaine.com>"
  exit 1
fi

if ! command -v dig >/dev/null 2>&1 && ! command -v nslookup >/dev/null 2>&1; then
  echo "❌ ni 'dig' ni 'nslookup' ne sont disponibles."
  exit 1
fi

OK=0
FAIL=0

check() {
  local label="$1" value="$2"
  if [ -n "$value" ]; then
    echo "✅ $label"
    echo "   -> $value"
    OK=$((OK+1))
  else
    echo "❌ $label — introuvable (propagation DNS en cours ou enregistrement absent ?)"
    FAIL=$((FAIL+1))
  fi
}

echo "════════════════════════════════════════════════════"
echo " Vérification DNS Resend — $DOMAIN"
echo "════════════════════════════════════════════════════"

# --- MX : send.$DOMAIN -----------------------------------
MX=$(dig +short MX "send.$DOMAIN" 2>/dev/null | grep -i amazonses | head -1)
check "MX  send.$DOMAIN -> feedback-smtp (amazonses)" "$MX"

# --- SPF : TXT send.$DOMAIN ------------------------------
SPF=$(dig +short TXT "send.$DOMAIN" 2>/dev/null | tr -d '"' | grep -i "v=spf1" | head -1)
check "TXT send.$DOMAIN (SPF)" "$SPF"

# --- DKIM : TXT resend._domainkey.$DOMAIN ----------------
DKIM=$(dig +short TXT "resend._domainkey.$DOMAIN" 2>/dev/null | tr -d '"' | grep -i "^p=" | head -1)
if [ -z "$DKIM" ]; then
  DKIM=$(dig +short TXT "resend._domainkey.$DOMAIN" 2>/dev/null | head -1)
fi
if [ -n "$DKIM" ]; then
  check "TXT resend._domainkey.$DOMAIN (DKIM)" "${DKIM:0:60}..."
else
  check "TXT resend._domainkey.$DOMAIN (DKIM)" ""
fi

# --- DMARC (recommandé) -----------------------------------
DMARC=$(dig +short TXT "_dmarc.$DOMAIN" 2>/dev/null | tr -d '"' | grep -i "v=dmarc1" | head -1)
check "TXT _dmarc.$DOMAIN (DMARC)" "$DMARC"

echo "════════════════════════════════════════════════════"
echo "Résultat : $OK enregistrement(s) OK, $FAIL manquant(s)."
if [ "$FAIL" -gt 0 ]; then
  echo "→ Attendre la propagation (jusqu'à 24h) puis relancer ce script."
  echo "→ Vérifier que les valeurs collées correspondent EXACTEMENT à celles"
  echo "  affichées sur https://resend.com/domains (hôte = 'send' et"
  echo "  'resend._domainkey', PAS 'send.$DOMAIN')."
  exit 1
fi
echo "→ Le domaine est prêt : mettre MAIL_FROM_ADDRESS dans .env puis"
echo "  lancer : php artisan config:clear"
exit 0
