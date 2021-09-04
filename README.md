
## Dokumentation:

Nutzt Slate zum Erstellen der Doku
`https://github.com/slatedocs/slate/wiki/Using-Slate-in-Vagrant`

Ins Verzeichnis `Docs` wecheln, dann vagrant starten:
`vagrant up`
Doku ist erreichbar unter `http://localhost:4567`

Zum Erstellen der statischen HTML-Dokumente: 
`vagrant ssh -c "cd /vagrant; bundle exec middleman build"`

Vagrant beenden:
`vagrant halt`