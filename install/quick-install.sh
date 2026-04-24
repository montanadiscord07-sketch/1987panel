#!/bin/bash
# ══════════════════════════════════════════════════════
#  1987 Panel — Hızlı Kurulum Scripti
#  Tek komutla kurulum
# ══════════════════════════════════════════════════════

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}"
cat << "EOF"
  ╔═══════════════════════════════════════════════════════╗
  ║                                                       ║
  ║              1987 PANEL - HIZLI KURULUM              ║
  ║                                                       ║
  ║         Modern Web Hosting Yönetim Paneli            ║
  ║                                                       ║
  ╚═══════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"
echo ""

# Root kontrolü
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Bu script root olarak çalıştırılmalı!"
    echo "Çalıştır: sudo bash quick-install.sh"
    exit 1
fi

# Ubuntu kontrolü
if [ -f /etc/os-release ]; then
    . /etc/os-release
    if [[ "$ID" != "ubuntu" ]]; then
        echo "❌ Bu script sadece Ubuntu için tasarlanmıştır"
        exit 1
    fi
fi

echo "📦 Panel dosyaları indiriliyor..."
cd /opt

# GitHub'dan indir (veya lokal yol kullan)
if [ -d "1987panel" ]; then
    echo "⚠️  1987panel dizini zaten mevcut!"
    read -p "Üzerine yazmak istiyor musunuz? (e/h): " confirm
    if [[ "$confirm" == "e" ]]; then
        rm -rf 1987panel
    else
        exit 0
    fi
fi

# Git ile klonla (veya wget ile indir)
if command -v git &> /dev/null; then
    git clone https://github.com/kullanici/1987panel.git 2>/dev/null || {
        echo "⚠️  Git klonlama başarısız, wget ile deneniyor..."
        wget -q https://github.com/kullanici/1987panel/archive/main.zip
        unzip -q main.zip
        mv 1987panel-main 1987panel
        rm main.zip
    }
else
    echo "📥 Git bulunamadı, wget ile indiriliyor..."
    apt update -qq
    apt install -y -qq wget unzip
    wget -q https://github.com/kullanici/1987panel/archive/main.zip
    unzip -q main.zip
    mv 1987panel-main 1987panel
    rm main.zip
fi

echo "✅ Panel dosyaları indirildi"
echo ""

# Kurulum scriptini çalıştır
cd /opt/1987panel/install
chmod +x setup.sh

echo "🚀 Ana kurulum başlatılıyor..."
echo ""
sleep 2

bash setup.sh
