# DG Festival Program

WordPress plugin fesztivál és családi nap program megjelenítéséhez, szerkesztői admin felülettel.

## Használat

1. Másold a `dg-festival-program` mappát a WordPress `wp-content/plugins/` könyvtárába.
2. Aktiváld a bővítményt a WordPress admin felületen.
3. A bal oldali admin menüben nyisd meg a `DG Program` oldalt.
4. Add meg, módosítsd vagy töröld a helyszíneket. Ezek lesznek a táblázat oszlopai az `Időpont` után.
5. Add meg, módosítsd vagy töröld az időpontokat és az időpontokhoz tartozó helyszínes programokat.
6. A tag-eket külön szerkesztheted: bővíthetők, átírhatók és törölhetők.
7. A programoknál a tag opcionális, választható a `Nincs tag` érték is.
8. Ha egy program több idősávon át tart, add meg a `Vége` időpontot. Desktop nézetben a cella összevonva jelenik meg.
9. Illeszd be a shortcode-ot egy oldalba vagy bejegyzésbe:

```text
[dg_program]
```

## Fájlok

- `dg-festival-program.php` - plugin belépési pont és shortcode
- `templates/program.php` - adatvezérelt program HTML template
- `assets/css/dg-festival-program.css` - megjelenés
- `assets/js/dg-festival-program.js` - tooltip fókuszkezelés
