# FIPE Crawler

Script que realiza download dos dados da [Tabela FIPE Preço Médio de Veículos](http://veiculos.fipe.org.br/).

Licensa: [MIT](LICENSE.md)

## Instalação

**Requisitos**

*   Versão linha de comando: PHP + PHP-CLI, MySQL, [Composer](getcomposer.org).
*   Versão Web: PHP, Apache, MySQL, [Composer](getcomposer.org).
*   Desenvolvimento versão Web: [NodeJS](https://nodejs.org) e [Bower](https://bower.io)

### Instalação versão linha de comando

```bash
apt-get install php5 php5-cli php-mysql php5-curl php5-json mysql-server-5.5
git clone https://github.com/rafaelgou/fipe-crawler.git
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```

### Instalação versão Web

```bash
apt-get install php5 php5-cli php-mysql php5-curl php5-json mysql-server-5.5 apache2 libapache2-mod-php5
git clone https://github.com/rafaelgou/fipe-crawler.git
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```
Para desenvolvimento utilize também `bower install` (exige [NodeJS](https://nodejs.org) ).

A versão Web foi construída [AngularJS](http://angularjs.org) v1.3.20, ou seja,
está bastante desatualizada. Utilize preferencialmente a versão de linha de
comando.

### Configuração

```bash
cd fipecrawler/config
cp config.dist.php config.php
```

Editar informações de acesso ao banco e URL para versão Web:

```php
<?php
$db = array(
    'host'   => 'localhost',
    'dbname' => 'fipe',
    'user'   => 'root',
    'pass'   => 'senha',
);
```

Crie a tabela `veiculo` no banco de dados com o seguinte comando:

```bash
mysql fipe -u root -p < sql/veiculo.sql
```

## Execução via linha de comando

Na raiz do sistema, execute:

```bash
./fipecrawler
```

que exibe ajuda padrão e lista de comandos do sistema:

```bash
veiculo
 veiculo:csv       Exporta arquivo CSV por ano, mês e tipo
 veiculo:extrair   Extrai tabela por ano, mês e tipo
```

Possível então:

```bash
./fipecrawler veiculo:extrair
./fipecrawler veiculo:csv
```

### Extraindo tabelas

```bash
./fipecrawler veiculo:extrair 2015 3 Caminhão
```

ou

```bash
./fipecrawler veiculo:extrair
```

o que pergunta:

```bash
--------------------------------------------------------------------------------

  FIPE Crawler
  veiculo:extrair
  Extrai tabela por ano, mês e tipo

--------------------------------------------------------------------------------
Informe ano (ENTER para 2015)
  [2015] 2015
  [2014] 2014
  [2013] 2013
  [2012] 2012
  [2011] 2011
  [2010] 2010
  [2009] 2009
  [2008] 2008
  [2007] 2007
  [2006] 2006
  [2005] 2005
  [2004] 2004
  [2003] 2003
  [2002] 2002
  [2001] 2001
 >
Informe mês (1 a 12) (ENTER para 03)
  [1 ] 1
  [2 ] 2
  [3 ] 3
  [4 ] 4
  [5 ] 5
  [6 ] 6
  [7 ] 7
  [8 ] 8
  [9 ] 9
  [10] 10
  [11] 11
  [12] 12
 >
Informe tipo (1 = carro, 2 = moto, 3 = caminhão) (ENTER para Carro)
  [1] Carro
  [2] Moto
  [3] Caminhão
 >
```

após, realiza a extração e salva em banco. Este procedimento é demorado,
e depende da velocidade de sua conexão e disponibilidade do site da FIPE.

Você pode acompanhar o progresso nesta tela:

```bash
Recuperando tabelas para 03/2015...
Encontrada tabela 03/2015 !

Recuperando marcas para tabela id=[176] 03/2015, tipo=[1] Carro...
Encontradas 87 marcas para tabela id=[176] 03/2015, tipo=[1] Carro !

Recuperando modelos para 87 marcas -- tabela id=[176] 03/2015, tipo=[1] Carro...

 87/87 [============================] 4562 modelos extraídos
Encontrados 4562 modelos para 87 marcas -- tabela id=[176] 03/2015, tipo=[1] Carro !

Recuperando veiculos para para 4562 -- tabela id=[176] 03/2015, tipo=[1] Carro...
    6/4562 [>---------------------------] 36 veículos extraídos
```

Neste ponto os dados já podem ser vistos no banco de dados. Como a tabela
possui chave única para `fipe_cod + anomod`, não há duplicação, mesmo se rodar
mais de uma vez para o mesmo período.

### Exportando CSV tabelas

Executado da mesma forma que o `veiculo:extrair`, mas solicita o
nome do arquivo de exportação.

```bash
./fipecrawler veiculo:csv 2015 3 Caminhão
```

ou

```bash
./fipecrawler veiculo:csv
```

Exemplo:

```bash
./fipecrawler veiculo:csv 2015 3 Caminhão
--------------------------------------------------------------------------------

  FIPE Crawler
  veiculo:csv
  Exporta arquivo CSV por ano, mês e tipo

--------------------------------------------------------------------------------
Informe nome do arquivo (padrao 'fipe_201503_Caminhão.csv'):
--------------------------------------------------------------------------------

  FIPE Crawler
  veiculo:csv
  Exporta arquivo CSV por ano, mês e tipo

--------------------------------------------------------------------------------

Recuperando veículos para tabela 03/2015, tipo=[3] Caminhão...
Encontrados 78 veículos para tabela 03/2015, tipo=[3] Caminhão
 78/78 [============================] veículos exportados
Exportados 78 veículos para tabela 03/2015, tipo=[3] Caminhão !
Tentando salvar arquivo /var/www/Clientes/DiegoVeiga/Fipe/fipecrawler/fipe_201503_Caminhão.csv...
Criado arquivo /var/www/Clientes/DiegoVeiga/Fipe/fipecrawler/fipe_201503_Caminhão.csv !

```

Arquivo exemplo:

```bash
fipe_cod,tabela_id,anoref,mesref,tipo,marca_id,marca,modelo_id,modelo,anomod,comb_cod,comb_sigla,comb,valor
501034-9,176,2015,3,3,102,Agrale,5986,10000 2P (Diesel) (E5),32000,3,D,Diesel,134625
501034-9,176,2015,3,3,102,Agrale,5986,10000 2P (Diesel) (E5),2015,3,D,Diesel,119985
501034-9,176,2015,3,3,102,Agrale,5986,10000 2P (Diesel) (E5),2014,3,D,Diesel,115125
501034-9,176,2015,3,3,102,Agrale,5986,10000 2P (Diesel) (E5),2013,3,D,Diesel,109087
501034-9,176,2015,3,3,102,Agrale,5986,10000 2P (Diesel) (E5),2012,3,D,Diesel,102992
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2012,3,D,Diesel,104763
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2011,3,D,Diesel,97221
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2010,3,D,Diesel,88952
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2009,3,D,Diesel,82993
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2008,3,D,Diesel,79413
501027-6,176,2015,3,3,102,Agrale,4448,13000 Turbo 2P (Diesel),2007,3,D,Diesel,72479
```

## Versão WEB

Descompacte/clone o conteúdo na raiz de sua árvore web. Opcionalmente você pode criar um VirtualHost,
link simbólico ou alias para o diretório `web/`.

Considerando que você utiliza o Apache2, necessita:

```bash
composer install
```

```bash
cd fipecrawler/config
cp config.dist.php config.php
```

Editar informações de acesso ao banco:

```php
<?php
$db = array(
    'host'   => 'localhost',
    'dbname' => 'fipe',
    'user'   => 'root',
    'pass'   => 'senha',
);
```

Considerando que foi descompatado na raiz, você terá a interface web navegando em
[http://localhost/fipecrawler/web](http://localhost/fipecrawler/web).

Se quiser navegar na versão de desenvolvimento, utilize
[http://localhost/fipecrawler/web/index_dev.php](http://localhost/fipecrawler/web/index_dev.php).

## Consultas

Verifique a tabela [veiculo](sql/veiculo.sql) para a estrutura de dados.

Informações úteis podem ser conseguidas com as seguintes consultas:

*   Lista de marcas

```sql
SELECT DISTINCT marca_id, marca FROM veiculo ORDER BY marca;
```

*   Lista de marcas e modelos

```sql
SELECT DISTINCT marca_id, marca, modelo_id, modelo FROM veiculo ORDER BY marca, modelo;
```

*   Filtrar por tipo (1 = carro, 2 = moto, 3 = caminhão)

```sql
-- Selecionando carros
SELECT * FROM veiculo WHERE tipo = 1;
```

*   Lista de combustíveis

```sql
SELECT DISTINCT comb_sigla, comb FROM veiculo ORDER BY comb_sigla, comb;
```


```bash
$ time ./fipecrawler veiculo:extrair 2023 1 Carro
--------------------------------------------------------------------------------
                                                                                
  FIPE Crawler                                                                  
  veiculo:extrair                                                               
  Extrai tabela por ano, mês e tipo                                            
                                                                                
--------------------------------------------------------------------------------

Recuperando tabelas para 01/2023...
Encontrada tabela 01/2023 !

Recuperando marcas para tabela id=[293] 01/2023, tipo=[1] Carro...
Encontradas 92 marcas para tabela id=[293] 01/2023, tipo=[1] Carro !

Recuperando modelos para 92 marcas -- tabela id=[293] 01/2023, tipo=[1] Carro...

 92/92 [============================] 6533 modelos extraídos
Encontrados 6533 modelos para 92 marcas -- tabela id=[293] 01/2023, tipo=[1] Carro !

Recuperando veiculos para para 6533 -- tabela id=[293] 01/2023, tipo=[1] Carro...
 6533/6533 [============================] 27251 veículos extraídos
Extraídos 27251 veículos -- tabela id=[293] 01/2023, tipo=[1] Carro !

--------------------------------------------------------------------------------
                                                                                
FIPE Crawler executado com sucesso em 14h2m9s, memória 8 megabytes             
                                                                                
--------------------------------------------------------------------------------
FIPE Crawler executado com sucesso!


real	69m57.767s
user	4m42.890s
sys	0m18.617s

```