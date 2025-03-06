# 📝 WP Migrations

![Packagist Version](https://img.shields.io/packagist/v/combizera/wordpress-to-laravel-migrator)
![Downloads](https://img.shields.io/packagist/dt/combizera/wordpress-to-laravel-migrator)
![License](https://img.shields.io/github/license/combizera/wordpress-to-laravel-migrator)
![PHP Version](https://img.shields.io/packagist/php-v/combizera/wordpress-to-laravel-migrator)

O **WP Migrations** é um pacote para **migrar postagens do WordPress para Laravel** de maneira simples e eficiente.

---

## 🚀 Instalação

Para instalar via **Composer**, execute o seguinte comando:

```bash
composer require combizera/wordpress-to-laravel-migrator
```

Após a instalação, publique o arquivo de configuração para personalizar o comportamento do pacote:

```bash
php artisan vendor:publish --tag=wp-migration-config
```

---

## 📌 Informações Importantes

🔹 **O que é migrado?**
- ✅ **Postagens do WordPress**
- ❌ **Imagens** (por enquanto, não são migradas)
- ❌ **Páginas do WordPress** (por enquanto, apenas posts são suportados)

🔹 **Requisitos obrigatórios:**
- A **Model de postagens** deve se chamar **`Post`** (ou ser configurada no `wp-migration.php`).
- A Model **deve conter os seguintes campos** no banco de dados:
    - `category_id` (integer)
    - `title` (string)
    - `slug` (string)
    - `content` (text)
    - `created_at` (timestamp)
    - `updated_at` (timestamp)
- **Atributos fillable:** Certifique-se de que sua `Post` Model possui os campos acima em `$fillable`.
- Ter um **arquivo `.xml`** exportado do WordPress com as postagens.

---

## 📚 Como Usar

1️⃣ **Tenha o XML exportado do WordPress**  
Para exportar suas postagens, vá até **Ferramentas > Exportar** no painel do WordPress e gere um arquivo `.xml`.

2️⃣ **Execute a migração:**  
Após instalar o pacote e configurar sua Model de `Post`, basta rodar o comando:

```bash
php artisan wp:migrate database/migration.xml
```

Isso irá processar o arquivo e criar os posts no seu banco de dados.

---

## ⚙️ Configuração Opcional

O pacote permite personalizar algumas configurações publicando o arquivo `wp-migration.php`:

```bash
php artisan vendor:publish --tag=wp-migration-config
```

O arquivo de configuração (`config/wp-migration.php`) permite alterar:
```php
return [
    'post_model' => App\Models\Post::class,         // Model de postagens
    'category_model' => App\Models\Category::class, // Model de categorias
    'default_user_id' => 1,                         // Usuário padrão que receberá os posts
];
```

---

## 🤝 Contribuições

Se quiser ajudar a **melhorar** este pacote:

1. Faça um **fork** do repositório.
2. Crie uma **branch** para sua feature:
   ```bash
   git checkout -b feat/#issue-number-feature-name
   # Exemplo: git checkout -b feat/#42-upgrade-config-file
   ```
3. Faça suas alterações e **commite**:
   ```bash
   git commit -m "feat(File): Add new feature"
   ```
4. Envie um **pull request** e aguarde!

Sinta-se à vontade para **abrir issues** caso tenha dúvidas ou sugestões! 🚀

---

## 📝 Licença

Este projeto é licenciado sob a **MIT License**. Sinta-se livre para usá-lo e modificá-lo conforme necessário. 

Viva o **Open Source**! 🎉

---
