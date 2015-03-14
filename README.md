# FIPE Crawler

Script que realiza download dos dados do site da FIPE [http://www2.fipe.org.br/pt-br/indices/veiculos/].

## Instalação versão linha de comando

### Requisitos


#### LINUX

Pacotes `php5`, `php5-cli`, `php-mysql`, `php5-curl`, `php5-json`.

Para Ubuntu/Debian, pode ser instalado com:

~~~bash
apt-get install php5 php5-cli php-mysql php5-curl php5-json
~~~

Descompactar o arquivo em um diretório:

~~~bash
tar xzvf fipecrawler.tar.gz
~~~

Alterar permissão do arquivo executável:

~~~bash
chmod 755 ./fipecrawler
~~~

#### Windows

Descompactar arquivo

~~~bash
unzip fipecrawler.zip
~~~

Editar caminho para executar PHP no arquivo `fipecrawler.bat`:

~~~bash
\CAMINHO\ATE\PHP\php src\fipecrawler.php
~~~

### Banco de DAdos

Configurar banco de dados:

~~~bash
cd fipecrawler/config
cp config.dist.php config.php
~~~

Editar informações de acesso ao banco:

~~~php
<?php
$db = array(
    'host'   => 'localhost',
    'dbname' => 'fipe',
    'user'   => 'root',
    'pass'   => 'codeloco',
);
$baseUrl = 'http://localhost/fipecrawler/web/';
~~~

`$baseUrl` não é utilizada pela linha de comando.

Na versão atual utilize o arquivo `sql/veiculo.sql` para criar a tabela `veiculo_completo`.

Pode ser instalado em qualquer banco MySQL, devidamente configurado no arquivo `config/config.php`.

## Execução via linha de comando

Na raiz do sistema, execute:

~~~bash
./fipecrawler
~~~

que exibe ajuda padrão e lista de comandos do sistema:

~~~bash
veiculo
 veiculo:csv       Exporta arquivo CSV por ano, mês e tipo
 veiculo:extrair   Extrai tabela por ano, mês e tipo
~~~

Para verificar se a configuração mínima está funcional.

### Extraindo tabelas

Execute o comando informando ano, mês e tipo (Carro, Moto, Caminhão, case sensitive),
ou use a sintaxe interativa, que pergunta item por item.

~~~bash
./fipecrawler veiculo:extrair 2015 3 Caminhão
~~~

ou

~~~bash
./fipecrawler veiculo:extrair
~~~

que pergunta:

~~~bash
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
~~~

após, realiza a extração e salva em banco. Este procedimento é demorado, e depende da
velocidade de sua conexão e disponibilidade do site da FIPE.

Você pode acompanhar o progresso nesta tela:

~~~bash
Recuperando tabelas para 03/2015...
Encontrada tabela 03/2015 !

Recuperando marcas para tabela id=[176] 03/2015, tipo=[1] Carro...
Encontradas 87 marcas para tabela id=[176] 03/2015, tipo=[1] Carro !

Recuperando modelos para 87 marcas -- tabela id=[176] 03/2015, tipo=[1] Carro...

 87/87 [============================] 4562 modelos extraídos
Encontrados 4562 modelos para 87 marcas -- tabela id=[176] 03/2015, tipo=[1] Carro !

Recuperando veiculos para para 4562 -- tabela id=[176] 03/2015, tipo=[1] Carro...
    6/4562 [>---------------------------] 36 veículos extraídos
~~~

Neste ponto os dados já podem ser vistos no banco de dados. Como a tabela
possui chave única para `fipe_cod + anomod`, não há duplicação, mesmo se rodar
mais de uma vez para o mesmo período.

### Exportando CSV tabelas

Executado da mesma forma que o `veiculo:extrair`, mas solicita o nome do arquivo de exportação.

~~~bash
./fipecrawler veiculo:csv 2015 3 Caminhão
~~~

ou

~~~bash
./fipecrawler veiculo:csv
~~~

Exemplo:

~~~bash
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

~~~

Arquivo exemplo:

~~~bash
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
~~~

## Versão WEB

TODO
libapache2-mod-php5