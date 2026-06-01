# Convite Andre & Monica

Sistema de convite de casamento virtual em PHP e MySQL.

## Recursos

- Convite individual por token
- Confirmação por adultos e crianças
- Painel administrativo
- Cadastro, edição e exclusão de convites
- Envio via webhook n8n
- Exportação CSV
- Lista de presentes
- Login administrativo
- SweetAlert2 no painel e no convite

## Variáveis de ambiente

```env
APP_URL=https://andreemonica.innovakode.com.br

DB_HOST=mysql
DB_NAME=convite
DB_USER=mysql
DB_PASSWORD=sua_senha

N8N_WEBHOOK_URL=https://sua-url-n8n/webhook/convite-casamento
```

## Deploy

O projeto roda via Dockerfile usando PHP 8.2 com Apache.