v0.3
v0.4
	Esta implementação está incompleta!!! A intenção era fazer o cadastro populando dinamicamente os selects de Máquina (este bem sucedido), Sequência e Nível (estes último fracassados, devido à dependencia um do outro). Usando o código js para fazer um chamado ajax para as funções que fazem o GET na api leadlovers pegando as opções e atualizando os selects com as novas escolhas, o que deu certo! Infelizmente não consegui validar estes itens ao salvar pois os campos dos selects não atualizam ao salvar.
	
v0.5
	Aqui damos um passo atrás devido ao mau funcionamento da versão anterior e tiramos os selects de máquina, sequencia e nivel, voltando aos campos de texto.  Isso pq não estamos conseguindo salvar os selects dinamicamente durante a captura dos campos pelo ajax no arquivo js. As configurações continuam no código como comentários com a tag ###RETOMAR. Utilizamos de volta os campos de texto onde inserimos o código id dos mesmos que devem ser encontrados lá nas configurações mais obscuras do leadlovers. Na verdade pode-se criar um webhook e usar os campos definidos lá.
	Testamos a inserção de campos dinâmicos do leadlovers --- mas até agora nada! :(
	For isso está funcionando!!!
	
v0.6
	Ok... descobri qual era o problema com os campos dinâmicos. Um array de arrays. Tb descobri que é possível mandar somente um código como campo dinâmico e inserí-lo como parâmetro dentro de um link no email. Sendo assim passaremos a usar esse código que indica a semana de origem do lead nos campos dinâmicos e usando-o como parâmetro. Não usaremos mais as tags para fazer esses filtros em trocentas máquinas... Basta uma sequencia de email.
	Quanto a salvar os itens select dinâmicos nada ainda!!!! Fica pra uma versão futura...

v0.7
	Agora é o seguinte: para poder unificar todos os cadastros e poder indicar no Campo Dinâmico a data de término (que varia de produto para produto) e não mais a de entrada inserimos o campo "Somar à Semana do Ano"... assim no formulário podemos indicar qual semana depois do cadastro será marcada no campo dinamico.
	E já que estamos usando o campo dinâmico e não a Tag para marcar a semana de entrada, capturamos as tag e as listamos em um select ao invés de indicar por texto.

v0.7.1
	Correção de bug (ao resgatar informações de configuração deve-se usar o método do G_Forms rgar() para evitar retorno de variáveis com erro a partir do PHP 8.0)

v0.8
	Reimplementação da proposta da v0.4 (abandonada) da captura dos códigos de Máquina/Sequência/Nível em selects por api leadlovers.
	Porém diferentemente da v.0.4 usamos campos hidden para salvar os valores enquanto usamos campos select somente no frontend.
	>> Bug!! << 
		Ainda ocorre um bug: ao salvar os dados os campos select não carregam as informações salvas, mas a versão previamente carregada...
		Porém, os dados estão salvos!!! Se a página for recarregada os selects serão atualizados corretamente! 
	Também deixamos tag e campo dinâmico opcionais.
	E corrigimos warnings.