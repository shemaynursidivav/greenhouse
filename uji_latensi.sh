#!/bin/bash
# =============================================================
#  Pengujian Latensi Sistem Monitoring Greenhouse
#  Dijalankan di VPS (server produksi)
#
#  Pakai:  cd /var/www/greenhouse && bash uji_latensi.sh
#  Hasil:  hasil_latensi.csv
# =============================================================

N=20
API_KEY="$(grep '^GANTRY_API_KEY=' .env | cut -d= -f2)"
GANTRY="$(grep '^GANTRY_DASHBOARD_URL=' .env | cut -d= -f2)"
LOCAL="http://127.0.0.1"

OUT="hasil_latensi.csv"
echo "no,titik_ukur,http_code,latensi_ms" > "$OUT"

ukur() {   # $1=label  $2=url  $3=header(opsional)
    local total=0 ok=0
    for i in $(seq 1 $N); do
        if [ -n "$3" ]; then
            res=$(curl -s -o /dev/null -w '%{http_code} %{time_total}' -H "$3" -m 15 "$2")
        else
            res=$(curl -s -o /dev/null -w '%{http_code} %{time_total}' -m 15 "$2")
        fi
        code=$(echo "$res" | cut -d' ' -f1)
        t=$(echo "$res" | cut -d' ' -f2)
        ms=$(echo "$t * 1000" | bc | cut -d. -f1)
        echo "$i,$1,$code,$ms" >> "$OUT"
        total=$((total + ms)); ok=$((ok + 1))
        printf "  %2d/%d  HTTP %s  %6s ms\n" "$i" "$N" "$code" "$ms"
        sleep 0.3
    done
    echo "  ---- rata-rata: $((total / ok)) ms"
    echo ""
}

echo "=================================================="
echo " UJI LATENSI  (n=$N per titik ukur)"
echo " Waktu : $(date '+%d/%m/%Y %H:%M:%S %Z')"
echo " Lokasi: VPS $(hostname)"
echo "=================================================="
echo ""

echo "[1] Latensi jaringan ke subsistem gantry (ping)"
ping -c 10 -q "$(echo "$GANTRY" | sed -e 's|http://||' -e 's|:.*||')" 2>/dev/null | tail -2
echo ""

echo "[2] Akuisisi data: dashboard -> API subsistem gantry"
ukur "akuisisi_gantry" "$GANTRY/api/partner/sessions" "X-API-Key: $API_KEY"

echo "[3] Respons server dashboard (halaman login)"
ukur "respons_server" "$LOCAL/login" ""

echo "[4] Penyajian data live (endpoint JSON)"
ukur "penyajian_live" "$LOCAL/gantry/live/data" ""

echo "=================================================="
echo " RINGKASAN  (untuk tabel Bab 4)"
echo "=================================================="
printf "%-20s %6s %8s %8s %8s\n" "Titik Ukur" "HTTP" "Min(ms)" "Maks(ms)" "Rata2(ms)"
printf "%-20s %6s %8s %8s %8s\n" "--------------------" "------" "--------" "--------" "---------"
for label in akuisisi_gantry respons_server penyajian_live; do
    awk -F, -v L="$label" '$2==L {
        s+=$4; n++; c=$3;
        if (min=="" || $4<min) min=$4;
        if ($4>max) max=$4
    } END { if (n>0) printf "%-20s %6s %8d %8d %8d\n", L, c, min, max, s/n }' "$OUT"
done
echo ""
echo "Catatan: HTTP 302 berarti endpoint memerlukan autentikasi."
echo "Data rinci tersimpan di: $OUT"