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

# fungsi tunggal yang dijalankan tiap proses paralel
satu_permintaan() {
    if [ -n "$UJI_HDR" ]; then
        curl -s -o /dev/null -w "%{http_code} %{time_total}\n" -H "$UJI_HDR" -m 20 "$UJI_URL"
    else
        curl -s -o /dev/null -w "%{http_code} %{time_total}\n" -m 20 "$UJI_URL"
    fi
}
export -f satu_permintaan

uji() {   # $1=label  $2=url  $3=header(opsional)
    echo "-- $1"
    export UJI_URL="$2"
    export UJI_HDR="$3"

    local tmp; tmp=$(mktemp)
    local mulai; mulai=$(date +%s.%N)

    seq 1 $N | xargs -P $C -I{} bash -c 'satu_permintaan' > "$tmp"

    local selesai; selesai=$(date +%s.%N)
    local durasi;  durasi=$(echo "$selesai - $mulai" | bc)

    local ok gagal
    ok=$(awk '$1>=200 && $1<400' "$tmp" | wc -l)
    gagal=$((N - ok))

    read -r avg min max <<< "$(awk '{ms=$2*1000; s+=ms; n++; if(mn==""||ms<mn)mn=ms; if(ms>mx)mx=ms}
        END{ if(n>0) printf "%d %d %d", s/n, mn, mx; else print "0 0 0" }' "$tmp")"

    local rps; rps=$(echo "scale=1; $N / $durasi" | bc)

    printf "   kode HTTP  : %s\n" "$(awk '{print $1}' "$tmp" | sort | uniq -c | tr '\n' ' ')"
    printf "   total %d | berhasil %d | gagal %d\n" "$N" "$ok" "$gagal"
    printf "   respons  min %d ms | maks %d ms | rata-rata %d ms\n" "$min" "$max" "$avg"
    printf "   durasi %.1f s  ->  %s permintaan/detik\n\n" "$durasi" "$rps"

    echo "$1,$N,$ok,$gagal,$avg,$min,$max,$rps" >> "$OUT"
    rm -f "$tmp"
    unset UJI_URL UJI_HDR
}

echo "=================================================="
echo " UJI THROUGHPUT"
echo " n = $N permintaan, concurrency = $C"
echo " Waktu : $(date '+%d/%m/%Y %H:%M:%S %Z')"
echo "=================================================="
echo ""

uji "halaman_login" "$LOCAL/login"                 ""
uji "endpoint_data" "$LOCAL/gantry/live/data"      ""
uji "api_gantry"    "$GANTRY/api/partner/sessions" "X-API-Key: $API_KEY"

echo "=================================================="
echo " RINGKASAN  (untuk Tabel 4.6)"
echo "=================================================="
printf "%-16s %6s %9s %6s %10s %9s\n" "Endpoint" "Total" "Berhasil" "Gagal" "Rata2(ms)" "Req/dtk"
printf "%-16s %6s %9s %6s %10s %9s\n" "----------------" "------" "---------" "------" "----------" "---------"
tail -n +2 "$OUT" | awk -F, '{printf "%-16s %6s %9s %6s %10s %9s\n", $1,$2,$3,$4,$5,$8}'
echo ""
echo "Catatan: kode 302 dihitung berhasil (pengalihan ke halaman login)."
echo "Data rinci: $OUT"
echo ""
echo "Beban server setelah pengujian:"
uptime