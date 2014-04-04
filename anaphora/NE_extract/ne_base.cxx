#encoding "utf-8"    // кодировка
#GRAMMAR_ROOT S

//TODO: перечисления(+\- готово, есть мусор) и сложные именные
//вопросы: вершины в количественных и предложных + в прошлом году
//+описать особенности

//базовые группы

BefAdj -> 'очень' | 'не';
Base_num -> AnyWord<wff="[0-9]+-[а-яx]{1,3}">; //5-го, 60-x
N -> Noun<~fw,l-reg,kwtype=~[bad_noun]>; //base
N -> Noun<~fw, h-reg1, l-quoted,kwtype=~[bad_noun]>; //base
N -> Noun<fw,h-reg1,wfm="[^А-Я]",kwtype=~[bad_noun]>;
N -> QuoteDbl Noun<rt,l-reg,kwtype=~[bad_noun]> QuoteDbl; //base
N -> 'гран-при'<h-reg1> | 'главред'<h-reg1>;
N -> Word<gram="SPRO",kwtype=~[pronoun]>;
ANP -> (BefAdj+) Word<gram="A",rt>+; // base
ANP -> ANP<gnc-agr[1]> 'и' ANP<gnc-agr[1]>; // base
ANP -> Word<gram="ANUM",gc-agr[1]>+ ('и') (Word<gram="ANUM", gc-agr[1]>); // base
ANP -> Word<gram="APRO"> ANP*; 

//ANP -> 'тот'<gram="m"> interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");

NP -> ANP<gc-agr[1], gram="sg"> N<rt,gc-agr[1], gram="sg"> interp (NamedEntity.Main::not_norm); 
NP -> ANP<c-agr[1]> N<rt,c-agr[1], gram="pl"> interp (NamedEntity.Main::not_norm); 

NP -> N interp (NamedEntity.Main::not_norm);
NP -> Base_num<gnc-agr[1]> N<rt,gnc-agr[1]> interp (NamedEntity.Main::not_norm); 
NP -> 'прошлый'<gram="abl"> N interp (NamedEntity.Main::not_norm){weight=1.5}; 

//особенное "друг друга"
//ComplPron -> 'друг' interp (ComplexNE3.Main::not_norm) 'дружка'<gram="~nom">;
ComplPron_part -> 'друг' interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
ComplPron_part -> 'дружка' interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
ComplPron -> ComplPron_part interp (ComplexNE3.Main::not_norm) ComplPron_part<gram="~nom">;
ComplPron_pr -> Pr interp (ComplexNE.Main) ComplPron_part;
ComplPron -> ComplPron_part interp (ComplexNE3.Main::not_norm) ComplPron_pr interp (ComplexNE.Self::not_norm; ComplexNE.Type="предложная");;

//количественные

NUM -> AnyWord<wff="[0-9]+,?[0-9]*">;
NUM -> 'много'<gram="~comp">;
Roman_Num -> AnyWord<wff="^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$">;
NUMNP -> NUM interp (NamedEntity.Main::not_norm);
NUMNP -> Word<gram="NUM,~comp">* Word<gram="NUM,~comp"> interp (NamedEntity.Main::not_norm); // base, depends

//цифра перед годом обычно обозначает порядковое числительное, т.е. группа базовая
Num -> AnyWord<wff="[0-9]+,?[0-9]*">; //борьба с лишней интерпретацией
NumSpec -> AnyWord<wff="[2-4]">;
Month -> 'январь' | 'февраль' | 'март' | 'апрель' | 'май' | 'июнь' | 'июль' | 'август' | 'сентябрь' | 'октябрь' | 'ноябрь' | 'декабрь' | 'класс'<gram="sg">;
Year -> 'год'<gram="sg">;
Cent -> 'век';
Base_NP -> Num Month interp (NamedEntity.Main::not_norm) {weight = 1.8};
Base_NP -> Num Year interp (NamedEntity.Main::not_norm) {weight = 1.8};
Base_NP -> Roman_Num Cent interp (NamedEntity.Main::not_norm) {weight = 1.8};
Spec_NP -> NumSpec Month interp (NamedEntity.Main::not_norm) {weight = 2.0};
Spec_NP -> NumSpec Year interp (NamedEntity.Main::not_norm) {weight = 2.0};


//имена собственные
Sobst_simpl -> Word<gram="S, ~abbr",~fw,h-reg1,~l-quoted> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm); 
Sobst_simpl -> UnknownPOS<h-reg1> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm);
Sobst -> Sobst_simpl (Roman_Num); //сюда может входить Пётр I

Sobst -> Word<h-reg1, quoted> interp (NamedEntity.Main::not_norm); //имя собств
Sobst_lat -> Word<h-reg1,lat> interp (NamedEntity.Main::not_norm); //имя собств
Sobst_lat -> Word<h-reg1,lat> Word<h-reg1,lat>+ interp (NamedEntity.Main="ALL"); //имя собств
Sobst -> Sobst_lat;

Sobst -> AnyWord<wff="([a-z]{3,10}://)?(www|ввв)?\\.?([A-Za-zА-Яа-я0-9-_]+\\.?){1,4}\\.[a-zа-я]{2,6}"> interp (NamedEntity.Main); //сайт
Sobst_site -> AnyWord<wff="([a-z]{3,10}://)?(www|ввв)?\\.?([А-Яа-я0-9-_]+\\.?){1,4}"> Punct 'рф'; //сайт
Sobst -> Sobst_site interp (NamedEntity.Main);

Quote -> QuoteDbl | QuoteSng;
Sobst -> Quote Sobst<rt> Quote;
Sobst_fw -> Word<gram="persn"> | Word<gram="famn"> | Word<gram="patrn"> | Word<gram="geo">; //имя собств
Sobst -> Sobst_fw<h-reg1> interp (NamedEntity.Main::not_norm) (Roman_Num);


Sobst -> Quote Word<gram="A", gnc-agr[1], h-reg1> Word<gram="A">* Noun<rt,gnc-agr[1]> interp (NamedEntity.Main::not_norm) Quote; //"Новая газета"
Sobst -> Word<gram="A", gnc-agr[1], ~fw, h-reg1> Noun<rt,gnc-agr[1]> interp (NamedEntity.Main::not_norm); //Невский проспект
Sobst -> Word<gram="A,~brev", gnc-agr[1]>+ Sobst<rt,gnc-agr[1]> interp (NamedEntity.Main::not_norm); //израильский Тель-Авив
//Sobst -> Sobst_fw<gnc-agr[1], ~fw, h-reg1> Noun<gnc-agr[1]> interp (NamedEntity.Main::not_norm); //Карловы вары

Sobst_init -> AnyWord<wff="[А-Я]\\."> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm);

//сложная - собственное наименование
Sobst_name -> (Sobst_fw<gnc-agr[1]>) (Sobst_simpl<gnc-agr[1]>) Sobst<rt,gram="persn", gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst<gram="persn">) Sobst_simpl<gnc-agr[1]>+ {weight=1.3};
Sobst_name -> Sobst_simpl<gnc-agr[1]> Sobst<rt,gram="persn", gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst<gram="persn">) (Sobst_simpl<gnc-agr[1]>) {weight=1.3};
Sobst_name -> Sobst_fw<gnc-agr[1]> Sobst<rt,gram="persn", gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst<gram="persn">) (Sobst_simpl<gnc-agr[1]>+) {weight=1.3};
//Sobst_name -> Sobst<gram="persn",gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) Sobst_simpl<gnc-agr[1]>; //канонический вариант
Sobst_name -> Sobst_simpl<gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) Sobst_simpl<gnc-agr[1]>; //Кими Р. (не можем определить Кими как persn, Райкконена вообще не знаем
//с инициалами
Sobst_name -> Sobst_init interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst_init) Sobst_simpl<rt>; //К. Райконнен
Sobst_name -> Sobst_simpl Sobst_init interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst_init); // Райконнен К.
Sobst_name -> Quote Sobst_name<rt> Quote; //"Евгений Онегин"
Sobst_name -> Word<gram="A", gnc-agr[1]>+ Sobst_name<rt,gnc-agr[1]>;


//сложная - несобственное наименование
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst_name<c-agr[1], GU=~[gen]> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="собственное наименование");
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst<c-agr[1], GU=~[gen]> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Att_NE<c-agr[1], GU=~[gen]> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение"); 

NeSobst_name -> Abbr interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NeSobst_name -> NP<rt> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst_lat interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"); 

//сложная - предложная (без предл. ИГ + предл. ИГ)
Pr -> Prep | Compl_PR;

Pr_NE_simpl -> Pr interp (ComplexNE.Main::not_norm) NE<rt>;
Pr_NE -> Pr interp (ComplexNE.Main::not_norm) Compl_NE<rt>;
Pr_NE_compl -> Pr interp (ComplexNE.Main::not_norm) Compl_NE6<rt>;
Pr_NE -> Pr_NE_simpl;

Pr_NE_part -> Pr interp (ComplexNE4.Main::not_norm) NE<rt>;
Pr_NE_part -> Pr interp (ComplexNE4.Main::not_norm) Compl_NE<rt>;
Pr_NE -> Pr_NE_part interp (ComplexNE.Main::not_norm; ComplexNE4.Self::not_norm; ComplexNE4.Type="предложная") Pr_NE_part<gram="gen"> interp (ComplexNE4.Self::not_norm; ComplexNE4.Type="предложная"); 

//сложная - количественная
Num_Noun -> 'тысяча' | 'миллион' | 'миллиард' | 'большинство' | 'млн' | 'млрд' | 'триллион' | 'трлн';
NUMNP_int -> NUMNP interp (NamedEntity.Type="количественная"; NamedEntity.Self::not_norm);
Num_with_Noun -> (NUMNP_int) Num_Noun interp (NamedEntity.Main::not_norm;NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") {weight=1.3};
Num_NE -> NUMNP_int NP<rt> interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная";ComplexNE2.Main::not_norm);
Num_NE -> Num_with_Noun+ (NUMNP_int) NP<rt> interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная";ComplexNE2.Main::not_norm);

//сложная - приложение
Att_noun -> 'статья' | 'глава' | 'параграф' | 'часть' | 'пункт' | 'раздел' | 'номер';
Att_short -> 'ст' | 'рис'| AnyWord<wff="[чп]">; //не берутся
Att_noun -> Att_short Punct;
Att_NE -> (Word<gram="A", gnc-agr[1]>+) Att_noun<gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"; NamedEntity.Main::not_norm) NUM {weight=1.8};
Att_NE -> Sobst interp (ComplexNE2.Main::not_norm; NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное") LBracket Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное") RBracket; //Раскассе (La Raskasse)
Att_NE -> NP<nc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Hyphen NP<nc-agr[1]> interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"); //заяц-беляк

//сложная - перечисление
//NE -> Att_NE | Num_NE | Pr_NE | NP | Base_NP | Num_NE | Sobst | Sobst_name | Sobst_title | NeSobst_name;
Enum_part -> Pr_NE<c-agr[1]> Word<gram="CONJ"> Pr_NE<c-agr[1]>;
Enum_part -> Pr_NE<c-agr[1]> Comma Enum_part<c-agr[1]>;
Enum_part_uno -> Pr_NE<c-agr[1]> Word<gram="CONJ"> NE<c-agr[1]>;

Enum_part -> NE<c-agr[1]> Word<gram="CONJ"> NE<c-agr[1]>;
Enum_part -> NE<c-agr[1]> Comma Enum_part<c-agr[1]>;
Enum_NE -> Enum_part interp (ComplexNE3.Main="ALL"; ComplexNE3.Self::not_norm; ComplexNE3.Type="перечисление");
Enum_NE -> Enum_part_uno interp (ComplexNE3.Main="ALL"; ComplexNE3.Self::not_norm; ComplexNE3.Type="перечисление");

//сложная - именная
NE_part -> NE interp (ComplexNE5.Main::not_norm) NE<gram="gen">;
NE_part -> NE interp (ComplexNE5.Main::not_norm) Compl_NE7<gram="gen">;
NE_part_pr -> NE interp (ComplexNE6.Main::not_norm) NE<gram="gen">;
NE_part_pr7 -> NE interp (ComplexNE7.Main::not_norm) NE<gram="gen">;
NE_part -> NE interp (ComplexNE5.Main::not_norm) Pr_NE_compl<gram="gen"> interp (ComplexNE.Self::not_norm; ComplexNE.Type="предложная");
NE_part -> NE interp (ComplexNE5.Main::not_norm) Pr_NE_simpl<gram="gen"> interp (ComplexNE.Self::not_norm; ComplexNE.Type="предложная");
NeSobst -> Abbr interp (ComplexNE1.Main::not_norm; NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NE_part -> NE interp (ComplexNE5.Main::not_norm) NeSobst interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="несобственное наименование"); 

Pr_part -> 'с' interp (ComplexNE.Main) NE<gram="anim">;
NE_part -> NE<gram="anim"> Pr_part interp (ComplexNE5.Main="ALL"; ComplexNE.Self::not_norm; ComplexNE.Type="предложная");
Compl_NE -> NE_part interp (ComplexNE5.Self::not_norm; ComplexNE5.Type="сложная именная"); 
Compl_NE6 -> NE_part_pr interp (ComplexNE6.Self::not_norm; ComplexNE6.Type="сложная именная"); 
Compl_NE7 -> NE_part_pr7 interp (ComplexNE7.Self::not_norm; ComplexNE7.Type="сложная именная"); 

//выражения без контекста
Compl_PR -> Word<kwtype="complex_prep">;
Compl_ADV -> Word<kwtype="complex_adv">;
Compl_ADV -> Word<wff="[Вв]"> 'прошлое'<gram="abl"> {weight=1.5};
Compl_CONJ -> Word<kwtype="complex_conj"> {weight=1.5};
Introduct -> Word<kwtype="introduct">;
IntroStop -> Comma | EOSent;


Abbr -> AnyWord<wff="[А-Я][А-Я]+">;
Abbr -> AnyWord<wff="[А-Я]+[а-я][А-Я]+">;
NE -> Abbr interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm);  

S -> Compl_PR interp (NamedEntity.Main="NONE"; NamedEntity.Self::not_norm; NamedEntity.Type="сложный предлог");
S -> Compl_ADV interp (NamedEntity.Main="NONE"; NamedEntity.Self::not_norm; NamedEntity.Type="наречное выражение");
S -> Compl_CONJ interp (NamedEntity.Main="NONE"; NamedEntity.Self::not_norm; NamedEntity.Type="составной союз");
S -> Introduct interp (NamedEntity.Main="NONE"; NamedEntity.Self::not_norm; NamedEntity.Type="вводное выражение") IntroStop;

NE -> NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
NE -> Base_NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
NE -> Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NE -> Spec_NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="специальная");


Ana_pron -> Word<kwtype="pronoun"> interp (NamedEntity.Main::not_norm) {weight=1.8};
Ana_pron_spec -> 'тот' | 'этот'; 
Ana_pron -> Ana_pron_spec<~gnc-agr[1]> interp (NamedEntity.Main::not_norm) NP<~gnc-agr[1]>;

S -> Ana_pron interp (NamedEntity.Self::not_norm; NamedEntity.Type="анафорическое местоимение"); 
//Снимаем омонимию для "что" и "чему"
NotSPRO -> 'что' | 'чем' | 'то'; 
S -> Comma NotSPRO interp (NamedEntity.Main="NONE";NamedEntity.Self::not_norm; NamedEntity.Type="составной союз") {weight=1.3}; 
S -> NotSPRO NP<gram="nom"> interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") {weight=1.3};
S -> Adv NotSPRO interp (NamedEntity.Main="NONE";NamedEntity.Self::not_norm; NamedEntity.Type="составной союз") {weight=1.3}; 

//BadNP -> 'а' | 'и' | 'в' | 'уже';
//BadNE -> BadNP<fw,h-reg1> interp (NamedEntity.Main="NONE";NamedEntity.Self::not_norm; NamedEntity.Type="плохая группа") {weight=1.2};
//S -> BadNE;

//------------сложные группы------------

NE -> NeSobst_name interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="несобственное наименование");
//NE -> Sobst_title interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="собственное наименование");
NE -> Sobst_name interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="собственное наименование");
NE -> Num_NE interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="сложная количественная");
NE -> Att_NE interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение");
NE -> ComplPron interp (ComplexNE3.Self::not_norm; ComplexNE3.Type="сложное местоимение");

S -> NE;
S -> Compl_NE;
S -> Enum_NE;
S -> Pr_NE interp (ComplexNE.Self::not_norm; ComplexNE.Type="предложная");
