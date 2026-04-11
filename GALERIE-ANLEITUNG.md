# Mosaik-Galerie — Anleitung für Admins

## Übersicht

Die Bildergalerie auf der Startseite ist ein horizontal scrollbarer Mosaik-Slider, angelehnt an die WIX Pro Gallery. Bilder werden in einem Zwei-Reihen-Layout mit unterschiedlichen Größen angeordnet.

Die Galerie wird in `layouts/index.html` konfiguriert. Die gesamte Steuerung erfolgt über HTML-Attribute — kein CSS- oder JavaScript-Wissen nötig.

---

## Bildposition und -größe (data-Attribute)

Jedes Bild ist ein `<div class="mosaic-item">` mit vier Steuer-Attributen:

| Attribut | Bedeutung | Einheit | Beispiel |
|----------|-----------|---------|----------|
| `data-x` | Horizontale Position (Abstand vom linken Rand) | Pixel | `data-x="366"` |
| `data-y` | Vertikale Position (Abstand von oben) | Prozent (0–100) | `data-y="56"` |
| `data-w` | Breite des Bildes | Pixel | `data-w="260"` |
| `data-h` | Höhe des Bildes | Prozent der Gesamthöhe | `data-h="44"` |

Die **Gesamthöhe** der Galerie ist 384px (Desktop). Prozentwerte beziehen sich immer darauf.

### Beispiel: Ein Bild nimmt die oberen 55% einer Spalte ein

```html
<div class="mosaic-item" data-x="366" data-y="0" data-w="260" data-h="55">
    <img src="/img/gallery/gallery-4.jpg" alt="Beschreibung" loading="lazy">
</div>
```

### Beispiel: Das Bild darunter füllt die restlichen 44%

```html
<div class="mosaic-item" data-x="366" data-y="56" data-w="260" data-h="44">
    <img src="/img/gallery/gallery-5.jpg" alt="Beschreibung" loading="lazy">
</div>
```

(1% Abstand zwischen 55% und 56% ergibt den Gap.)

---

## Bildausschnitt steuern (object-position)

Wenn ein Bild zugeschnitten wird und wichtige Teile (z.B. Köpfe) abgeschnitten werden, kann der sichtbare Ausschnitt mit `style="object-position: ..."` auf dem `<img>` Tag gesteuert werden.

### Häufige Werte

| Wert | Wirkung |
|------|---------|
| `center center` | Standard — Bildmitte wird gezeigt |
| `center top` | Oberer Rand sichtbar (Köpfe bleiben drin) |
| `center bottom` | Unterer Rand sichtbar |
| `left center` | Linke Seite betont |
| `center 30%` | 30% von oben — gut für Gruppenfotos |
| `20% 10%` | Präzise: 20% von links, 10% von oben |

### Beispiel

```html
<img src="/img/gallery/gallery-7.jpg" alt="Tänzerinnen" loading="lazy"
     style="object-position: center top;">
```

---

## Aktuelles Layout (8 Bilder)

```
Gruppe A (x: 0–361)           Gruppe B (x: 366–626)      Gruppe C (x: 631–821)    Gruppe D (x: 826–1106)
┌────────┬────────┐            ┌──────────────┐            ┌──────────┐              ┌────────────┐
│ Bild 1 │ Bild 2 │  35%       │   Bild 4     │  55%       │          │              │  Bild 6    │  65%
│ 178px  │ 178px  │            │   260px      │            │  Bild 8  │  100%        │  280px     │
├────────┴────────┤            ├──────────────┤            │  190px   │              ├────────────┤
│     Bild 3      │  64%       │   Bild 5     │  44%       │(Hochf.)  │              │  Bild 7    │  34%
│     361px       │            │   260px      │            │          │              │  280px     │
└─────────────────┘            └──────────────┘            └──────────┘              └────────────┘
```

---

## Neues Bild hinzufügen

1. Bild als JPG in `/static/img/gallery/` ablegen (z.B. `gallery-9.jpg`)
2. In `layouts/index.html` im Gallery-Block eine neue Zeile einfügen:

```html
<div class="mosaic-item" data-x="1111" data-y="0" data-w="250" data-h="50">
    <img src="/img/gallery/gallery-9.jpg" alt="Beschreibung" loading="lazy">
</div>
```

3. `data-x` so wählen, dass es rechts neben der letzten Gruppe beginnt (+ 5px Gap)
4. `data-y` und `data-h` passend zur gewünschten Zeile setzen
5. Hugo neu bauen: `hugo --minify`

---

## Bildgrößen-Empfehlungen

| Bildtyp | Empfohlene Quellgröße | Hinweis |
|---------|----------------------|---------|
| Querformat (1 Zeile) | 800×600 px | Standard |
| Breit (1 Zeile, doppelt breit) | 1200×600 px | Für Panoramen |
| Hochformat (2 Zeilen) | 600×900 px oder höher | Nutzt volle Galeriehöhe |

Bilder werden immer mit `object-fit: cover` angezeigt — sie füllen ihren Rahmen komplett aus und werden ggf. beschnitten. Deshalb ist `object-position` wichtig, um den richtigen Ausschnitt zu zeigen.

---

## Mobile Ansicht

Die Galerie skaliert auf Mobilgeräten automatisch herunter (280px bei Tablets, 200px auf Smartphones). Die Proportionen bleiben erhalten, das JavaScript berechnet die Positionen relativ zur Gesamthöhe.
