#!/bin/bash
# =============================================================
#  Pengujian Throughput / Beban Server Dashboard
#  Dijalankan di VPS (server produksi)
#
#  Pakai:  cd /var/www/greenhouse && bash uji_throughput.sh
#  Hasil:  hasil_throughput.csv
#
#  Catatan: tidak memerlukan Raspberry Pi menyala.
# =============================================================

N=100                 # jumlah permintaan per endpoint
C=10                  # permintaan serentak (concurrency)
LOCAL="http://127.0.0.1"
API_KEY="$(grep '^GANTRY_API_KEY=' .env | cut -d= -f2)"
GANTRY="$(grep '^GANTRY_DASHBOARD_URL=' .env | cut -d= -f2)"

OUT="hasil_throughput.csv"
echo "endpoint,total,berhasil,gagal,rata_ms,min_ms,maks_ms,rps" > "$OUT"

uji() {   # $1=label  $2=url  $3=header(opsional)
    echo "── $1"
    local tmp; tmp=$(mktemp)
    local mulai; mulai=$(date +%s.%N)

    seq 1 $N | xargs -P $C -I{} sh -c '
        if [ -n "$3" ]; then
            curl -s -o /dev/null -w "%{http_code} %{time_total}\n" -H "$3" -m 20 "$2"
        else
            curl -s -o /dev/null -w "%{http_code} %{time_total}\n" -m 20 "$2"
        fi
    ' _ "$2" "$3" >> "$tmp"

    local selesai; selesai=$(date +%s.%N)
    local durasi; durasi=$(echo "$selesai - $mulai" | bc)

    local ok gagal
    ok=$(awk '$1>=200 && $1<400' "$tmp" | wc -l)
    gagal=$((N - ok))

    read -r avg min max <<< "$(awk '{ms=$2*1000; s+=ms; n++; if(mn==""||ms<mn)mn=ms; if(ms>mx)mx=ms}
        END{printf "%d %d %d", s/n, mn, mx}' "$tmp")"

    local rps; rps=$(echo "scale=1; $N / $durasi" | bc)

    printf "   total %d | berhasil %d | gagal %d\n" "$N" "$ok" "$gagal"
    printf "   respons  min %d ms | maks %d ms | rata-rata %d ms\n" "$min" "$max" "$avg"
    printf "   durasi %.1f s  →  %s permintaan/detik\n\n" "$durasi" "$rps"

    echo "$1,$N,$ok,$gagal,$avg,$min,$max,$rps" >> "$OUT"
    rm -f "$tmp"
}

echo "=================================================="
echo " UJI THROUGHPUT"
echo " n = $N permintaan, concurrency = $C"
echo " Waktu : $(date '+%d/%m/%Y %H:%M:%S %Z')"
echo "=================================================="
echo ""

uji "halaman_login"    "$LOCAL/login"                       ""
uji "endpoint_data"    "$LOCAL/gantry/live/data"            ""
uji "api_gantry"       "$GANTRY/api/partner/sessions"       "X-API-Key: $API_KEY"

echo "=================================================="
echo " RINGKASAN  (untuk Tabel 4.6)"
echo "=================================================="
printf "%-18s %7s %9s %7s %9s %8s\n" "Endpoint" "Total" "Berhasil" "Gagal" "Rata2(ms)" "Req/dtk"
printf "%-18s %7s %9s %7s %9s %8s\n" "------------------" "-------" "---------" "-------" "---------" "--------"
tail -n +2 "$OUT" | awk -F, '{printf "%-18s %7s %9s %7s %9s %8s\n", $1,$2,$3,$4,$5,$8}'
echo ""
echo "Data rinci: $OUT"
echo ""
echo "Beban server saat ini:"
uptime