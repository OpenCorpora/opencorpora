#encoding "utf-8"    // кодировка
#GRAMMAR_ROOT S

//TODO: перечисления(+\- готово, есть мусор) и сложные именные
//вопросы: вершины в количественных и предложных + в прошлом году
//+описать особенности

//базовые группы

BefAdj -> 'очень' | 'не';
Base_num -> AnyWord<wff="[0-9]+-[а-яx]{1,3}">; //5-го, 60-x
N -> Noun<~fw,l-reg, no_hom>; //base
N -> Noun<~fw, h-reg1, l-quoted, no_hom>; //base
N -> Noun<fw,h-reg1,no_hom>;
N -> QuoteDbl Noun<l-reg> QuoteDbl; //base
N -> 'гран-при'<h-reg1>;
ANP -> (BefAdj) Word<gram="A">+; // base
ANP -> ANP<gnc-agr[1]> 'и' ANP<gnc-agr[1]>; // base
ANP -> Word<gram="ANUM",gc-agr[1]>+ ('и') (Word<gram="ANUM", gc-agr[1]>); // base
NP -> ANP<gc-agr[1]> N<rt,gc-agr[1]> interp (NamedEntity.Main::not_norm); 
NP -> N interp (NamedEntity.Main::not_norm);
NP -> Base_num<gnc-agr[1]> N<rt,gnc-agr[1]> interp (NamedEntity.Main::not_norm); 
NP -> 'друг' 'друг'<gram="gen">;
NP -> 'друг' 'дружка'<gram="gen">;
//ANP -> Word<gram="APRO"> interp (NamedEntity.Main::not_norm); // только для токенов - исходящих стрелок

//количественные

NUM -> AnyWord<wff="[0-9]+,?[0-9]*">;
Roman_Num -> AnyWord<wff="^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$">;
NUMNP -> NUM interp (NamedEntity.Self::not_norm; NamedEntity.Type="количественная")| Word<gram="NUM">+ interp (NamedEntity.Self::not_norm; NamedEntity.Type="количественная"); // base, depends

//цифра перед годом обычно обозначает порядковое числительное, т.е. группа базовая
Month -> 'январь' | 'февраль' | 'март' | 'апрель' | 'май' | 'июнь' | 'июль' | 'август' | 'сентябрь' | 'октябрь' | 'ноябрь' | 'декабрь' | 'класс'<gram="abl">;
Year -> 'год'<gram="sg">;
Cent -> 'век';
Base_NP -> NUM Month interp (NamedEntity.Main::not_norm) {weight = 1.8};
Base_NP -> NUM Year interp (NamedEntity.Main::not_norm) {weight = 1.8};
Base_NP -> Roman_Num Cent interp (NamedEntity.Main::not_norm) {weight = 1.8};

//имена собственные
Sobst_simpl -> Word<gram="S, ~abbr",~fw,h-reg1,~l-quoted> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm); 
Sobst_simpl -> UnknownPOS<~fw,h-reg1> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm);
Sobst -> Sobst_simpl (Roman_Num); //сюда может входить Пётр I

Sobst -> Word<h-reg1, quoted> interp (NamedEntity.Main::not_norm); //имя собств
Sobst -> Word<h-reg1,lat>+ interp (NamedEntity.Main::not_norm); //имя собств

Sobst -> AnyWord<wff="([a-z]{3,10}://)?(www|ввв)?\\.?([A-Za-zА-Яа-я0-9-_]+\\.?){1,4}\\.[a-zа-я]{2,6}"> interp (NamedEntity.Main); //сайт
Sobst_site -> AnyWord<wff="([a-z]{3,10}://)?(www|ввв)?\\.?([А-Яа-я0-9-_]+\\.?){1,4}"> Punct 'рф'; //сайт
Sobst -> Sobst_site interp (NamedEntity.Main);

Sobst -> QuoteDbl Sobst QuoteDbl;
Sobst -> QuoteSng Sobst QuoteSng;
Sobst_fw -> Word<gram="persn"> | Word<gram="famn"> | Word<gram="patrn"> | Word<gram="geo">; //имя собств
Sobst -> Sobst_fw<h-reg1> interp (NamedEntity.Main::not_norm) (Roman_Num interp (+NamedEntity.Main::not_norm));

Sobst -> Word<gram="A", gnc-agr[1], l-quoted, h-reg1> Word<gram="A">* Noun<gnc-agr[1], r-quoted> interp (NamedEntity.Main::not_norm); //"Новая газета"
Sobst -> Word<gram="A", gnc-agr[1], ~fw, h-reg1> Noun<gnc-agr[1]> interp (NamedEntity.Main::not_norm); //Невский проспект
Sobst -> Word<gram="A,~brev", gnc-agr[1]>+ Sobst<gnc-agr[1]> interp (NamedEntity.Main::not_norm); //израильский Тель-Авив

Sobst_init -> AnyWord<wff="[А-Я]\\."> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm);

//сложная - собственное наименование
Sobst_name -> (Sobst_simpl<gnc-agr[1]>) Sobst<gram="persn", gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst<gram="persn">) Sobst_simpl<gnc-agr[1]> {weight=1.3};
Sobst_name -> Sobst_simpl<gnc-agr[1]> Sobst<gram="persn", gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst<gram="persn">) (Sobst_simpl<gnc-agr[1]>) {weight=1.3};
Sobst_name -> Sobst_simpl<gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) Sobst_simpl<gnc-agr[1]>; //Кими Р. (не можем определить Кими как persn, Райкконена вообще не знаем
//с инициалами
Sobst_name -> Sobst_init interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst_init) Sobst_simpl; //К. Райконнен
Sobst_name -> Sobst_simpl Sobst_init interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное"; NamedEntity.Main::not_norm) (Sobst_init); // Райконнен К.
Sobst_name -> QuoteDbl Sobst_name QuoteDbl; //"Евгений Онегин"
Sobst_name -> QuoteSng Sobst_name QuoteSng; //"Евгений Онегин"
Sobst_name -> Word<gram="A", gnc-agr[1]>+ Sobst_name<gnc-agr[1]>;

//сложное название (книги, программы)
//Sobst_title -> QuoteDbl N<rt> Pr_NE QuoteDbl;
Sobst_title -> N<rt, h-reg1> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"; NamedEntity.Main::not_norm) Pr_NE;

//сложная - несобственное наименование
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst_name<c-agr[1], gram="~gen"> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="собственное наименование");
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst<c-agr[1], gram="~gen"> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NeSobst_name -> NP<rt,c-agr[1]> interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Att_NE<c-agr[1], gram="~gen"> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение"); 

NeSobst_name -> Abbr interp (ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
//1. для одушевлённых. Спасёт от группы "пилота Формулы-1"
/*
NeSobst_name -> NP<gnc-agr[1], gram="anim"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst_name<gnc-agr[1], gram="anim"> interp (ComplexNE.Self::not_norm; ComplexNE.Type="собственное наименование");
NeSobst_name -> NP<gnc-agr[1], gram="anim"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst<gnc-agr[1], gram="anim"> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NeSobst_name -> NP<gnc-agr[1], gram="anim"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Att_NE<gnc-agr[1], gram="anim"> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение"); 
//2. для неодушевлённых. Их тоже брать надо.
NeSobst_name -> NP<gnc-agr[1], gram="inan"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst_name<gnc-agr[1], gram="inan"> interp (ComplexNE.Self::not_norm; ComplexNE.Type="собственное наименование");
NeSobst_name -> NP<gnc-agr[1], gram="inan"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Sobst<gnc-agr[1], gram="inan"> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
NeSobst_name -> NP<gnc-agr[1], gram="inan"> interp (ComplexNE.Main::not_norm; ComplexNE1.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Att_NE<gnc-agr[1], gram="inan"> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение"); 
*/

//сложная - предложная (без предл. ИГ + предл. ИГ)
Pr -> Prep | Compl_PR;
/*
Pr_NE -> Pr NP<rt> interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
Pr_NE -> Pr Base_NP<rt> interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
Pr_NE -> Pr Sobst<rt> interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");
Pr_NE -> Pr Att_NE<rt> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение");
Pr_NE -> Pr Num_NE<rt> interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="сложная количественная");
Pr_NE -> Pr Compl_NE<rt> interp (ComplexNE5.Self::not_norm; ComplexNE5.Type="сложная именная");
Pr_NE -> Pr Sobst_name<rt> interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="собственное наименование");
Pr_NE -> Pr NeSobst_name<rt> interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="несобственное наименование");
*/

Pr_NE -> Pr NE<rt>;
Pr_NE -> Pr_NE interp (ComplexNE.Main::not_norm; ComplexNE4.Self::not_norm; ComplexNE4.Type="предложная") Pr_NE<gram="gen"> interp (ComplexNE4.Self::not_norm; ComplexNE4.Type="предложная"); 

//сложная - количественная
Num_Noun -> 'тысяча' | 'миллион' | 'миллиард' | 'большинство' | 'млн' | 'млрд' | 'триллион' | 'трлн';
Num_with_Noun -> (NUMNP) Num_Noun interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
Num_NE -> NUMNP NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"; NamedEntity.Main::not_norm;ComplexNE2.Main::not_norm);
Num_NE -> Num_with_Noun+ (NUMNP) NP interp (ComplexNE2.Main::not_norm);

//сложная - приложение
Att_noun -> 'статья' | 'глава' | 'параграф' | 'часть' | 'пункт' | 'раздел' | 'номер';
Att_short -> 'ст' | 'рис'| AnyWord<wff="[чп]">; //не берутся
Att_noun -> Att_short Punct;
Att_NE -> (Word<gram="A", gnc-agr[1]>+) Att_noun<gnc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"; NamedEntity.Main::not_norm) NUM {weight=1.8};
Att_NE -> Sobst interp (ComplexNE2.Main::not_norm; NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное") LBracket Sobst interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное") RBracket; //Раскассе (La Raskasse)
Att_NE -> NP<nc-agr[1]> interp (ComplexNE2.Main::not_norm; NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная") Hyphen NP<nc-agr[1]> interp (NamedEntity.Main::not_norm; NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная"); //заяц-беляк

//сложная - перечисление
//NE -> Att_NE | Num_NE | Pr_NE | NP | Base_NP | Num_NE | Sobst | Sobst_name | Sobst_title | NeSobst_name;
Enum_part -> NE<c-agr[1]> Word<gram="CONJ"> NE<c-agr[1]>;
Enum_part -> NE<c-agr[1]> Comma Enum_part<c-agr[1]>;
Enum_NE -> Enum_part interp (ComplexNE3.Main="ALL"; ComplexNE3.Self::not_norm; ComplexNE3.Type="перечисление");

//сложная - именная
NE_part -> NE interp (ComplexNE5.Main::not_norm) NE<gram="gen">;
NE -> NE_part interp (ComplexNE5.Self::not_norm; ComplexNE5.Type="сложная именная"); 

//выражения без контекста
Compl_PR -> Word<kwtype="complex_prep">;
Compl_ADV -> Word<kwtype="complex_adv">;
Compl_CONJ -> Word<kwtype="complex_conj">;
Introduct -> Word<kwtype="introduct">;
IntroStop -> Comma | EOSent;

Abbr -> AnyWord<wff="[А-Я][А-Я]+">;
NE -> Abbr interp (NamedEntity.Self::not_norm; NamedEntity.Type="аббревиатура"; NamedEntity.Main::not_norm);  

//S -> Compl_PR interp (NamedEntity.Self::not_norm; NamedEntity.Type="сложный предлог");
S -> Compl_ADV interp (NamedEntity.Self::not_norm; NamedEntity.Type="наречное выражение");
S -> Compl_CONJ interp (NamedEntity.Self::not_norm; NamedEntity.Type="составной союз");
S -> Introduct interp (NamedEntity.Self::not_norm; NamedEntity.Type="вводное выражение") IntroStop;

NE -> NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
NE -> Base_NP interp (NamedEntity.Self::not_norm; NamedEntity.Type="базовая именная");
NE -> Sobst interp (NamedEntity.Self::not_norm; NamedEntity.Type="имя собственное");

//------------сложные группы------------

NE -> Pr_NE interp (ComplexNE.Self::not_norm; ComplexNE.Type="предложная");
NE -> NeSobst_name interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="несобственное наименование");
NE -> Sobst_title interp (ComplexNE1.Self::not_norm; ComplexNE1.Type="собственное наименование");
NE -> Sobst_name interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="собственное наименование");
NE -> Num_NE interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="сложная количественная");
NE -> Att_NE interp (ComplexNE2.Self::not_norm; ComplexNE2.Type="приложение");

S -> NE;
S -> Enum_NE;
