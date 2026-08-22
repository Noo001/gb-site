#!/bin/bash
for i in $(seq 1 30); do
  ts=$(date '+%Y-%m-%d %H:%M:%S')
  cn=$(echo | openssl s_client -connect fr.gbsale.ru:443 -servername fr.gbsale.ru 2>/dev/null | openssl x509 -noout -subject 2>/dev/null | sed 's/^subject=CN = //')
  echo "$ts fr.gbsale.ru CN=$cn" >> c:/repos/gb-site/tmp/ssl-monitor.log
  if [ "$cn" = "fr.gbsale.ru" ]; then
    echo "$ts SSL OK" >> c:/repos/gb-site/tmp/ssl-monitor.log
    exit 0
  fi
  sleep 180
done
echo "$ts timeout" >> c:/repos/gb-site/tmp/ssl-monitor.log
