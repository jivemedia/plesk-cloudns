# plesk-cloudns

Automatically adds and removes slave DNS zones in ClouDNS when domains are created or deleted in Plesk.

## Table of Contents
- [What it does](#what-it-does)
- [What it does not](#what-it-does-not)
- [Requirements](#requirements)
- [Installation](#installation)
- [Basic troubleshooting](#basic-troubleshooting)
- [Debugging](#debugging)
- [Credits](#credits)
- [License](#license)

## What it does
- Creates a **slave** zone in ClouDNS for every new domain/alias created in Plesk.
- Deletes the corresponding ClouDNS slave zone when the domain/alias is removed.

## What it does not
- Configure Plesk’s DNS templates or ClouDNS nameservers.
- Offer a graphical interface - requires manual editing of the `ClouDNS.php` file.

## Requirements
- Plesk Obsidian **18.0.69+**.
- SSH access as **root** (or via `sudo`).
- PHP CLI with the `curl` extension.
- ClouDNS API user credentials (`auth-id` & `password`).

## Installation
1. Create an API user in ClouDNS (obtain `auth-id` & `password`).  
2. Copy `ClouDNS.php` to `/usr/local/psa/admin/plib/registry/EventListener/`.  
3. Edit `ClouDNS.php`, set your `authid`, `authkey`, and (optionally) `masterip`.  
4. (Optional) Set `$debug = true` for verbose logging.  
5. Done - enjoy automatic zone syncing!

## Basic troubleshooting
- Ensure your ClouDNS API user has both IPv4 & IPv6 allowed.  
- In Plesk’s DNS Settings (Tools & Settings → General Settings → DNS Settings → Transfer Restrictions Template), allow transfers to all your nameservers.

## Debugging
1. In `ClouDNS.php`, change `$debug = false;` to `$debug = true;`.  
2. `tail -f /var/log/plesk/panel.log` while creating/deleting a domain in Plesk.  

## Credits
- Forked from Nick Andriopoulos’s original [plesk-cloudns](https://github.com/lambdatwelve/plesk-cloudns).  
- Updated by Greg Sevastos for compatibility with Plesk 18.0.69+.

## License
This project is licensed under the [GNU Lesser General Public License v3.0](LICENSE).
