@extends('template/main')

@section('content')

<div class="bg-theme-1 bg-header">
    <div class="container text-center text-white">
        <h3>{{ $paket->nama_paket }}</h3>
    </div>
</div>

<div class="custom-shape-divider-top-1617767620">
    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V7.23C0,65.52,268.63,112.77,600,112.77S1200,65.52,1200,7.23V0Z" class="shape-fill"></path>
    </svg>
</div>

<div class="container main-container">
    @if($seleksi != null)
	    @if(strtotime('now') < strtotime($seleksi->waktu_wawancara))
	    <div class="row">
	        <div class="col-12 mb-2">
	            <div class="alert alert-danger fade show text-center" role="alert">
	                Tes akan dilaksanakan pada tanggal <strong>{{ setFullDate($seleksi->waktu_wawancara) }}</strong> mulai pukul <strong>{{ date('H:i:s', strtotime($seleksi->waktu_wawancara)) }}</strong>.
	            </div>
	        </div>
	    </div>
	    @endif
    @endif

	<div id="question" class="row" style="margin-bottom: 100px;">
		<!-- Button Navigation -->
		<div class="col-md-3 mb-3 mb-md-0">
			<div class="card">
				<div class="card-header fw-bold text-center">Navigasi Soal</div>
				<div class="card-body">
				</div>
			</div>
		</div>

		<!-- Question -->
		<div class="col-md-9">
			<div class="card card-question">
				<div class="card-header">
					<span class="fw-bold"><i class="fa fa-edit"></i> Soal</span>
				</div>
				<div class="card-body"></div>
				<div class="card-footer bg-white text-center"></div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-extra')

<!-- React JS -->
<script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
<script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

<script type="text/babel">
class App extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			test: 'ist',
			part: 5,
			parts: [],
			items: [],
			activeItem: '',
			activeNumber: 0,
			answers: [],
			doubts: []
		};
		this.handleButtonNavCallback = this.handleButtonNavCallback.bind(this);
		this.handleCardCallback = this.handleCardCallback.bind(this);
	}

	componentDidMount = () => {
		this.getRequest(this.state.test, this.state.part);
	}

	getRequest = (test, part) => {
		// Fetch data
		fetch('/api/question?test=' + test + '&part=' + part)
			.then(response => response.json())
			.then(data => {
					this.setState({
						parts: data.parts,
						items: data.questions,
						activeItem: data.questions[0],
						activeNumber: data.questions[0].nomor
					});
				}
			);
	}

	handleButtonNavCallback = (data) => {
		this.setState({
			activeItem: this.getItemByNumber(data.activeNumber),
			activeNumber: data.activeNumber,
		});
	}

	handleCardCallback = (data) => {
		this.setState({
			answers: data.answers,
			doubts: data.doubts,
		});

		if(data.activeNumber !== undefined) {
			this.setState({
				activeItem: this.getItemByNumber(data.activeNumber),
				activeNumber: data.activeNumber
			});
		}

		if(data.part !== undefined) {
			this.getRequest(this.state.test, data.part);
			this.setState({
				part: data.part
			});
		}
	}

	getNextPart = () => {
		const {parts, part} = this.state;
		let key;

		if(parts.length > 0) {
			for(let i = 0; i < parts.length; i++) {
				if(part === parts[i].part) {
					key = i;
				}
			}
		}

		return parts[key + 1];
	}

	getItemByNumber = (number) => {
		const {items} = this.state;
		let item;

		if(items.length > 0) {
			for(let i = 0; i < items.length; i++) {
				if(number === items[i].nomor) {
					item = items[i];
				}
			}
		}

		return item;
	}

	getPreviousItem = () => {
		const {items, activeNumber} = this.state;
		let item;

		if(items.length > 0) {
			for(let i = 0; i < items.length; i++) {
				if((activeNumber - 1) === items[i].nomor) {
					item = items[i];
				}
			}
		}

		return item;
	}

	getNextItem = () => {
		const {items, activeNumber} = this.state;
		let item;

		if(items.length > 0) {
			for(let i = 0; i < items.length; i++) {
				if((activeNumber + 1) === items[i].nomor) {
					item = items[i];
				}
			}
		}

		return item;
	}

	render = () => {
		const {items, activeItem, activeNumber, answers, doubts} = this.state;

		return (
			<React.Fragment>
				<div class="col-md-3 mb-3 mb-md-0" id="nav-button">
					<ButtonNav
						parentCallback={this.handleButtonNavCallback}
						items={items}
						activeNumber={activeNumber}
						answers={answers}
						doubts={doubts}
					/>
				</div>
				<div class="col-md-9">
					<Card
						parentCallback={this.handleCardCallback}
						item={activeItem}
						previousItem={this.getPreviousItem()}
						nextItem={this.getNextItem()}
						nextPart={this.getNextPart()}
					/>
				</div>
			</React.Fragment>
		);
	}
}

class ButtonNav extends React.Component {
	constructor(props) {
		super(props);
		this.handleClick = this.handleClick.bind(this);
	}

	handleClick = (number) => {
		// Callback to parent component
		this.props.parentCallback({
			activeNumber: number
		});
	}

	render = () => {
		const items = this.props.items;
		const activeNumber = this.props.activeNumber;
		const answers = this.props.answers;
		const doubts = this.props.doubts;

		return (
			<div class="card">
				<div class="card-header fw-bold text-center">Navigasi Soal</div>
				<div class="card-body">
					{
						items.map((item, index) => {
							// Set button color
							let buttonColor;
							if(doubts[item.nomor] === true) buttonColor = 'btn-warning';
							else if(answers[item.nomor] !== undefined && answers[item.nomor] !== '') buttonColor = 'btn-primary';
							else if(item.nomor === activeNumber && (answers[item.nomor] === undefined || answers[item.nomor] === '')) buttonColor = 'btn-info';
							else buttonColor = 'btn-outline-dark';

							return (
								<button
									class={`btn btn-sm ${buttonColor}`}
									onClick={() => this.handleClick(item.nomor)}
								>
									{item.nomor} ({answers[item.nomor] !== undefined && answers[item.nomor] !== '' ? item.tipe_soal === 'choice' || item.tipe_soal === 'image' ? answers[item.nomor] : 'Y' : '-' })
								</button>
							);
						})
					}
				</div>
			</div>
		);
	}
}

class Card extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			answers: [],
			doubts: []
		};
		this.handleChoiceCallback = this.handleChoiceCallback.bind(this);
		this.handleButtonDoubtCallback = this.handleButtonDoubtCallback.bind(this);
		this.handleButtonPreviousCallback = this.handleButtonPreviousCallback.bind(this);
		this.handleButtonNextCallback = this.handleButtonNextCallback.bind(this);
		this.handleButtonSubmitCallback = this.handleButtonSubmitCallback.bind(this);
	}

	handleChoiceCallback = (data) => {
		let {answers, doubts} = this.state;
		answers[data.number] = data.answer;

		// Update state
		this.setState({
			answers: answers
		});

		// Callback to parent component
		this.props.parentCallback({
			answers: answers,
			doubts: doubts
		});
	}

	handleCheckboxNumberCallback = (data) => {
		let {answers, doubts} = this.state;
		let answerTemp = answers[data.number];

		if(answerTemp === undefined) {
			answerTemp = [];
		}

		if(data.isChecked) {
			answerTemp.push(data.value);
			answers[data.number] = answerTemp;
		}

		// // Update state
		this.setState({
			answers: answers
		});

		// // Callback to parent component
		// this.props.parentCallback({
		// 	answers: answers,
		// 	doubts: doubts
		// });
	}

	handleButtonDoubtCallback = (data) => {
		let {answers, doubts} = this.state;
		doubts[data.number] = data.doubt;

		// Update state
		this.setState({
			doubts: doubts
		});

		// Callback to parent component
		this.props.parentCallback({
			answers: answers,
			doubts: doubts
		});
	}

	handleButtonPreviousCallback = (data) => {
		let {answers, doubts} = this.state;

		// Callback to parent component
		this.props.parentCallback({
			answers: answers,
			doubts: doubts,
			activeNumber: data.number
		});
	}

	handleButtonNextCallback = (data) => {
		let {answers, doubts} = this.state;

		// Callback to parent component
		this.props.parentCallback({
			answers: answers,
			doubts: doubts,
			activeNumber: data.number
		});
	}

	handleButtonSubmitCallback = (data) => {
		let {answers, doubts} = this.state;

		// Callback to parent component
		this.props.parentCallback({
			answers: answers,
			doubts: doubts,
			part: data.part
		});
	}

	renderForm = () => {
		const {answers, doubts} = this.state;
		const item = this.props.item;
		const question = this.props.item.soal;
		const choices = (question !== undefined && question[0].pilihan !== undefined) ? Object.entries(question[0].pilihan) : [];

		if(item.tipe_soal === 'choice') {
			return (
				choices.map((choice) => {
					return (
						<Choice
							parentCallback={this.handleChoiceCallback}
							number={item.nomor}
							option={choice[0]}
							description={choice[1]}
							isChecked={answers[item.nomor] === choice[0] ? true : false}
						/>
					)
				})
			);
		}
		else if(item.tipe_soal === 'essay') {
			return (
				<TextField
					parentCallback={this.handleChoiceCallback}
					number={item.nomor}
					value={answers[item.nomor] !== undefined ? answers[item.nomor] : ''}
				/>
			);
		}
		else if(item.tipe_soal === 'number') {
			return (
				<CheckboxNumber
					parentCallback={this.handleCheckboxNumberCallback}
					number={item.nomor}
					// isChecked={answers[item.nomor] === 1 ? true : false}
				/>
			);
		}
		else return null;
	}

	render = () => {
		const {answers, doubts} = this.state;
		const item = this.props.item;

		return (
			<div class="card card-question">
				<div class="card-header">
					<span class="fw-bold"><i class="fa fa-edit"></i> Soal {item.nomor}</span>
				</div>
				<div class="card-body">
					<p>{item.soal !== undefined ? item.soal[0].soal : ''}</p>
					{this.renderForm()}
				</div>
				<div class="card-footer bg-white text-center">
					<ButtonPrevious
						parentCallback={this.handleButtonPreviousCallback}
						number={this.props.previousItem !== undefined ? this.props.previousItem.nomor : 0}
					/>
					<ButtonDoubt
						parentCallback={this.handleButtonDoubtCallback}
						number={item.nomor}
						isDoubt={doubts[item.nomor] ? true : false}
					/>
					<ButtonSubmit
						parentCallback={this.handleButtonSubmitCallback}
						part={this.props.nextPart !== undefined ? this.props.nextPart.part : 0}
					/>
					<ButtonNext
						parentCallback={this.handleButtonNextCallback}
						number={this.props.nextItem !== undefined ? this.props.nextItem.nomor : 0}
					/>
				</div>
			</div>
		);
	}
}

class ButtonDoubt extends React.Component {
	constructor(props) {
		super(props);
		this.handleClick = this.handleClick.bind(this);
	}

	handleClick = (number) => {
		// Callback to parent component
		this.props.parentCallback({
			number: number,
			doubt: !this.props.isDoubt,
		});
	}

	render = () => {
		return (
			<button
				class="btn btn-sm btn-warning m-1"
				onClick={() => this.handleClick(this.props.number)}
			>
				<i class="fa fa-lightbulb-o me-1"></i>
				{this.props.isDoubt ? 'Yakin' : 'Ragu'}
			</button>
		);
	}
}

class ButtonPrevious extends React.Component {
	constructor(props) {
		super(props);
	}

	handleClick = (number) => {
		// Callback to parent component
		this.props.parentCallback({
			number: number
		});
	}

	render = () => {
		if(this.props.number > 0){
			return (
				<button
					class="btn btn-sm btn-primary m-1"
					onClick={() => this.handleClick(this.props.number)}
				>
					<i class="fa fa-chevron-left me-1"></i> Sebelumnya
				</button>
			);
		}
		else return null;
	}
}

class ButtonNext extends React.Component {
	constructor(props) {
		super(props);
	}

	handleClick = (number) => {
		// Callback to parent component
		this.props.parentCallback({
			number: number
		});
	}

	render = () => {
		if(this.props.number > 0){
			return (
				<button
					class="btn btn-sm btn-primary m-1"
					onClick={() => this.handleClick(this.props.number)}
				>
					Selanjutnya <i class="fa fa-chevron-right ms-1"></i>
				</button>
			);
		}
		else return null;
	}
}

class ButtonSubmit extends React.Component {
	constructor(props) {
		super(props);
		this.handleClick = this.handleClick.bind(this);
	}

	handleClick = () => {
		let ask = confirm("Anda yakin ingin mengumpulkan tes ini?");
		if(ask) {
			this.props.parentCallback({
				part: this.props.part
			});
		}
	}

	render = () => {
		return (
			<button
				class="btn btn-sm btn-info m-1"
				onClick={this.handleClick}
			>
				<i class="fa fa-save me-1"></i> Submit
			</button>
		);
	}
}

class Choice extends React.Component {
	constructor(props) {
		super(props);
		this.handleChange = this.handleChange.bind(this);
	}

	handleChange = (number, answer) => {
		// Callback to parent component
		this.props.parentCallback({
			number: number,
			answer: answer,
		});
	}

	render = () => {
		return (
			<div class="form-check">
				<input
					class="form-check-input"
					type="radio"
					name={`choice[${this.props.number}]`}
					id={`choice-${this.props.option}`}
					value={this.props.option}
					checked={this.props.isChecked}
					onChange={() => this.handleChange(this.props.number, this.props.option)}
				/>
				<label class="form-check-label" for={`choice-${this.props.option}`}>{this.props.description}</label>
			</div>
		);
	}
}

class TextField extends React.Component {
	constructor(props) {
		super(props);
		this.handleChange = this.handleChange.bind(this);
	}

	handleChange = (event) => {
		// Callback to parent component
		this.props.parentCallback({
			number: this.props.number,
			answer: event.target.value,
		});
	}

	render = () => {
		return (
			<textarea
				class="form-control form-control-sm"
				rows="1"
				placeholder="Jawaban Anda..."
				value={this.props.value}
				onChange={this.handleChange}
			/>
		);
	}
}

class CheckboxNumber extends React.Component {
	constructor(props) {
		super(props);
		this.handleChange = this.handleChange.bind(this);
	}

	handleChange = (event) => {
		// Callback to parent component
		this.props.parentCallback({
			number: this.props.number,
			value: event.target.value,
			isChecked: event.target.checked
		});
	}

	render = () => {
		let elements = [];
		for(var i = 0; i < 10; i++) {
			elements.push(
				<div class="form-check form-check-inline">
					<input
						class="form-check-input"
						type="checkbox"
						name={`checkbox[${this.props.number}]`}
						value={i}
						id={`checkbox-${i}`}
						checked={this.props.isChecked}
						onChange={this.handleChange}
					/>
					<label class="form-check-label" for={`checkbox-${i}`}>{i}</label>
				</div>
			);
		}
		return <React.Fragment>{elements}</React.Fragment>;
	}
}

// Render DOM
ReactDOM.render(<App/>, document.getElementById('question'));
</script>

@endsection

@section('css-extra')
<style type="text/css">
	.modal .modal-body {font-size: 14px; overflow-y: auto; max-height: calc(100vh - 200px);}
	.table {margin-bottom: 0;}
	.radio-image label {cursor: pointer;}
	.radio-image label.border-primary {border-color: var(--color-1)!important;}
	/* #form {filter: blur(3px);} */

	#nav-button {text-align: center;}
	#nav-button .btn {font-size: .75rem; width: 3.75rem; margin: .25rem;}
	#nav-button .btn:focus {box-shadow: none;}
</style>
@endsection