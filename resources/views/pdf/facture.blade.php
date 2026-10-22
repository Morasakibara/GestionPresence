<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture['numero'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; color: #080808; font-size: 11px; }
        .page { padding: 14px 18px; }
        .header { display: table; width: 100%; }
        .header .logo { display: table-cell; width: 68px; vertical-align: middle; }
        .header .logo img { width: 60px; height: auto; }
        .header .identite { display: table-cell; vertical-align: middle; }
        .header h1 { font-size: 18px; letter-spacing: 2px; color: #080808; }
        .header .contact { font-size: 9px; color: #555; margin-top: 2px; }
        .bandeau { background: #080808; color: #D39B23; padding: 6px 10px; margin-top: 10px; font-size: 8px; letter-spacing: 1px; }
        .bandeau span { color: #fff; font-weight: bold; }
        .top-info { display: table; width: 100%; margin-top: 8px; }
        .top-info .col { display: table-cell; width: 50%; vertical-align: top; }
        .top-info .label { font-size: 8px; text-transform: uppercase; color: #888; letter-spacing: 1px; }
        .top-info .valeur { font-size: 11px; font-weight: bold; }
        .mention { margin: 14px 0; padding: 12px 14px; text-align: center; border: 3px solid #0b7a34; border-radius: 6px; }
        .mention.acompte { border-color: #b35c00; background: #fdf3e4; }
        .mention.payee { border-color: #0b7a34; background: #eaf7ec; }
        .mention .titre { font-size: 10px; letter-spacing: 3px; color: #555; }
        .mention .montant { font-size: 22px; font-weight: bold; margin-top: 4px; }
        .mention.payee .montant { color: #0b7a34; }
        .mention.acompte .montant { color: #b35c00; }
        table.lignes { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.lignes th { background: #080808; color: #fff; font-size: 8px; text-transform: uppercase; letter-spacing: 1px; padding: 5px 8px; text-align: left; }
        table.lignes td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 10px; }
        table.lignes td.droite, table.lignes th.droite { text-align: right; }
        tfoot td { font-weight: bold; }
        .solde-flag { display: table; width: 100%; margin-top: 8px; border-collapse: collapse; }
        .solde-flag .cell { display: table-cell; width: 50%; padding: 8px; text-align: center; border: 1px solid #ddd; }
        .solde-flag .cell .k { font-size: 8px; text-transform: uppercase; color: #888; letter-spacing: 1px; }
        .solde-flag .cell .v { font-size: 13px; font-weight: bold; }
        .verrou { margin-top: 10px; background: #f5f5f5; border-left: 3px solid #080808; padding: 6px 10px; font-size: 8px; color: #333; }
        .pied { margin-top: 14px; border-top: 1px solid #ddd; padding-top: 6px; font-size: 7.5px; color: #888; display: table; width: 100%; }
        .pied .c { display: table-cell; width: 50%; vertical-align: top; }
        .signature { margin-top: 16px; display: table; width: 100%; }
        .signature .c { display: table-cell; width: 50%; text-align: center; }
        .signature .ligne { border-top: 1px solid #333; margin-top: 26px; padding-top: 3px; font-size: 8px; }
    </style>
</head>
<body>
<div class="page">

    {{-- En-tête entreprise --}}
    <div class="header">
        <div class="logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo">
            @endif
        </div>
        <div class="identite">
            <h1>{{ strtoupper($entreprise['nom']) }}</h1>
            <div class="contact">
                @if($entreprise['adresse']){{ $entreprise['adresse'] }} @endif
                @if($entreprise['phone'])— Tél. {{ $entreprise['phone'] }}@endif
            </div>
        </div>
    </div>

    <div class="bandeau">
        FACTURE <span>{{ $facture['numero'] }}</span>
        @if($facture['phase'] === 'solde') — REÇU FINAL @else — REÇU D'ACOMPTE @endif
    </div>

    {{-- Infos client / facture --}}
    <div class="top-info">
        <div class="col">
            <div class="label">Client</div>
            <div class="valeur">{{ $facture['client_nom'] ?: 'Client' }}</div>
            <div class="label" style="margin-top:4px;">WhatsApp</div>
            <div class="valeur">{{ $facture['client_whatsapp'] }}</div>
        </div>
        <div class="col" style="text-align:right;">
            <div class="label">Date</div>
            <div class="valeur">{{ \Carbon\Carbon::parse($facture['date'])->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- Mention dynamique selon l'avancement --}}
    @if((float) $facture['solde'] <= 0)
        <div class="mention payee">
            <div class="titre">FACTURE PAYÉE</div>
            <div class="montant">SOLDE : 0 {{ $entreprise['devise'] }}</div>
        </div>
    @else
        <div class="mention acompte">
            <div class="titre">ACOMPTE REÇU</div>
            <div class="montant">SOLDE À PAYER : {{ number_format($facture['solde'], 0, ',', ' ') }} {{ $entreprise['devise'] }}</div>
        </div>
    @endif

    {{-- Détail --}}
    <table class="lignes">
        <thead>
        <tr>
            <th>Désignation</th>
            <th class="droite">Montant total</th>
            <th class="droite">Acompte</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                <b>{{ ucfirst($facture['type']) }}</b>
                @if($facture['details'])<br><span style="color:#777; font-size:9px;">{{ $facture['details'] }}</span>@endif
            </td>
            <td class="droite">{{ number_format($facture['montant_total'], 0, ',', ' ') }} {{ $entreprise['devise'] }}</td>
            <td class="droite">{{ number_format($facture['montant_acompte'], 0, ',', ' ') }} {{ $entreprise['devise'] }}</td>
        </tr>
        </tbody>
    </table>

    <div class="solde-flag">
        <div class="cell">
            <div class="k">Payé</div>
            <div class="v">{{ number_format($facture['montant_acompte'], 0, ',', ' ') }} {{ $entreprise['devise'] }}</div>
        </div>
        <div class="cell">
            <div class="k">Reste à payer (verrouillé)</div>
            <div class="v" style="color:#b35c00;">{{ number_format($facture['solde'], 0, ',', ' ') }} {{ $entreprise['devise'] }}</div>
        </div>
    </div>

    {{-- Verrou d'intégrité --}}
    <div class="verrou">
        Document généré par le système — lecture seule. Empreinte SHA-256 : {{ substr($facture['hash'], 0, 32) }}…
        Toute altération invalide la facture (contrôle <span style="font-weight:bold;">{{ strtoupper($facture['numero']) }}</span> — {{ $facture['phase'] }}).
    </div>

    <div class="signature">
        <div class="c">
            <div class="ligne">Le caissier (Le Pharaon)</div>
        </div>
        <div class="c">
            <div class="ligne">Le client</div>
        </div>
    </div>

    <div class="pied">
        <div class="c">Export confidentiel — merci de conserver ce reçu.</div>
        <div class="c" style="text-align:right;">Merci de votre confiance 🙏</div>
    </div>

</div>
</body>
</html>
