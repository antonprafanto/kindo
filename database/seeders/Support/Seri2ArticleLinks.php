<?php

namespace Database\Seeders\Support;

/**
 * Helper slug artikel Seri 1 (#1–#10) & Seri 2 (#11–#39) — dipakai seeder/patch
 * supaya referensi "#13", "capstone", dll. konsisten jadi hyperlink internal.
 */
final class Seri2ArticleLinks
{
    /** @var array<int, string> nomor artikel → slug URL */
    public const SLUGS = [
        8  => 'kontrol-lampu-esp32-mqtt-relay',
        10 => 'dashboard-esp32-web-server-mqtt-monitoring-dht22',
        11 => 'deep-sleep-esp32-sensor-dht22-hemat-baterai',
        12 => 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode',
        13 => 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt',
        14 => 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c',
        15 => 'ota-update-firmware-esp32-via-wifi',
        16 => 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32',
        17 => 'mqtt-tls-qos-lwt-retained-mosquitto-esp32',
        18 => 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32',
        19 => 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt',
        21 => 'home-assistant-integrasi-esp32-mqtt',
        23 => 'node-red-dashboard-otomasi-iot-mqtt-esp32',
        24 => 'sensor-gerak-pir-esp32-lampu-mqtt-debounce',
        26 => 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh',
        28 => 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard',
        29 => 'migrasi-platformio-esp32-vscode-project-rapi',
        31 => 'freertos-esp32-multi-task-sensor-wifi-mqtt',
        33 => 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi',
        34 => 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt',
        35 => 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt',
        37 => 'sd-card-spi-esp32-logging-data-sensor-offline',
        38 => 'https-sertifikat-esp32-wificlientsecure-api-rest',
        39 => 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt',
    ];

    public static function link(int $num, ?string $label = null): string
    {
        $slug = self::SLUGS[$num] ?? null;
        $text = $label ?? "#{$num}";
        if ($slug === null) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        return '<a href="/artikel/' . $slug . '">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</a>';
    }

    /** @param int[] $nums */
    public static function links(array $nums): string
    {
        return implode(', ', array_map(fn (int $n) => self::link($n), $nums));
    }
}
