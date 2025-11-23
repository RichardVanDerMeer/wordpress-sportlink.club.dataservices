# Gutenberg Blocks Development

## Setup

Installeer dependencies:

```bash
npm install
```

## Development

Start development mode met auto-reload:

```bash
npm start
```

## Build voor Productie

Build de blocks voor productie:

```bash
npm run build
```

Dit genereert geoptimaliseerde bestanden in de `build/` directory.

## Beschikbare Blocks

### Sportlink Programma Block

Toont het wedstrijdprogramma met alle mogelijke filter- en weergaveopties.

**Instellingen:**

**Algemeen:**
- **Teamcode**: Specifiek team (optioneel, laat leeg voor alle teams)
- **Aantal wedstrijden**: Hoeveel wedstrijden tonen (1-100)
- **Aantal dagen vooruit**: Periode in dagen (1-365)
- **Week offset**: Relatieve week (-52 tot 52, 0 = huidige week)
- **Sorteervolgorde**: Datum of Team

**Filters:**
- **Eigen wedstrijden**: Ja/Nee
- **Thuiswedstrijden**: Ja/Nee
- **Uitwedstrijden**: Ja/Nee
- **Spelsoort**: Alles, Veld, Zaal
- **Competitiesoort**: Alles, Regulier, Beker
- **Dag**: Alles, Zaterdag, Zondag
- **Leeftijdscategorie**: Alles, Senioren, Junioren

**Geavanceerd:**
- **Gebruik lokale teamgegevens**: Gebruik aangepaste teamnamen

## Block Structuur

```
blocks/
├── programma/
│   ├── block.json          # Block metadata en attributes
│   ├── index.js            # Block registratie
│   ├── edit.js             # Editor component (React)
│   ├── editor.scss         # Editor styling
│   └── render.php          # Server-side rendering
```

## Development Tips

1. **Hot Reload**: Gebruik `npm start` tijdens development
2. **Linting**: Run `npm run lint:js` en `npm run lint:css`
3. **Formatting**: Auto-format met `npm run format`
4. **Testing**: Test in WordPress editor na build

## Deployment

Commit alleen de source files in `blocks/`, niet de `build/` directory.
Build tijdens deployment of handmatig voor een release.
