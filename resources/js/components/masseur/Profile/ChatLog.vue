<template>
<div class="chat p-3 pt-4" ref="chat">
	<transition leave-active-class="animated fadeOut fast">
			<loader v-if="!messagesLoaded"></loader>
	</transition>
	<div v-for="(message,index) in messages" :key="index">
		<div class="my-message" style="margin-top: 0.15rem" v-if="isThisMessageYours(message.author_id)">
			<div style="float:right" class="ml-2 message-text">{{ message.text }}</div>
			<div style="clear:both"></div>
			<span v-if="isThisLastMessageInThisBlock(index)" class="mt-2 mb-1" style="float:right;font-size:0.7em;padding-left: 0.5rem ">
				{{ message.msg_created_text }}
				&nbsp;<span><i class="fas fa-check" :class="{'seen':message.seen_at,'not-seen':!message.seen_at}"></i></span>
			</span>
			<div style="clear:both"></div>
		</div>
		<div class="message" style="margin-top: 0.15rem" v-else>

			<div style="float:left;max-width: 70%;position:relative;" >
				<img v-if="isThisLastMessageInThisBlock(index)" src="/storage/avatars/default.png"  class="rounded-circle avatar chat-avatar" style="float:left">
				<div v-if="isThisLastMessageInThisBlock(index)" style="margin-left:4.25rem" class="message-text" v-html="message.text"></div>
				<div v-else style="margin-left:4.25rem" class="message-text" v-html="message.text"></div>
				<div style="clear:both"></div>
				<span v-if="isThisLastMessageInThisBlock(index)" class="mb-1" style="margin-left:4.25rem;font-size:0.7em;padding-left: 0.5rem ">{{ message.msg_created_text }}</span>
			</div>
			<!--  -->
							<!-- </div> -->
			<div style="clear:both"></div>
		</div>
	</div>
	 <div class="message" v-if="friendTyping">
		<img src="/storage/avatars/default.png" class="rounded-circle avatar" style="float:left;width: 4rem;height: 4rem;">
		<div style="float:left">
			<div class="ml-2 message-text">
			    <span class="dot"></span>
			    <span class="dot"></span>
			    <span class="dot"></span>
			</div>
			<div style="clear:both"></div>
		</div>
		<div style="clear:both"></div>
	</div>


</div>
</template>
<style type="text/css" scoped>
	.chat-avatar{
	width: 4rem;
	height: 4rem;
	position:absolute;
  /* left: 0; */
  bottom: 1.1rem;
		/* display: inline-block; */
}

.chat{
	overflow-y: auto;
	overflow-x: hidden;
	max-height: 700px;
	min-height: 400px;
}

.seen{
	color: #42b883;
}
.not-seen{
	color: #ddd;
}

.message>div>.message-text{
	background-color: #f1f1f1;
	padding: 1rem;
	border-radius: 20px;
	/* display: inline-block; */
}


.my-message>.message-text{
	background-color: #f1f1f1;
	padding: 1rem;
	border-radius: 20px;
	max-width: 80%;
}
.dot {
  display: inline-block;
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #666;
  animation: wave 1.3s linear infinite;
}
.dot:nth-child(2) {
  animation-delay: -1.1s;
	background: #777;
}
.dot:nth-child(3) {
  animation-delay: -0.9s;
	background: #888;
}

@keyframes wave {
  0%,
	60%,
	100% {
    transform: initial;
  }
  30% {
    transform: translateY(-5px);
  }
}
</style>
<script type="text/javascript">
import loadingComponent from '../../others/LoadingComponent.vue';
export default{
	data(){
		return{
			messages:[],
			messagesLoaded: false,
		}
	},
	props:{
		userData: Object,
		friendId: Number,
		friendTyping : Boolean,
	},
	components:{
		'loader': loadingComponent,
	},
	mounted(){
		this.getMessages(this.friendId);

	},
	watch:{
		friendTyping: function(){
			this.$nextTick(() => {
				var container = this.$refs.chat;
				container.scrollTop = container.scrollHeight + 1000;
			});
		}
	},
	methods:{
		getMessages(friend_id){
			this.messagesLoaded = false,
			axios.post('/getMessages',{'friend_id': friend_id}).then((response)=>{
				this.messages = [];
				response.data.forEach(el => {
					this.messages.push({
						'author_id':el.sender_id,
						'text':el.message,
						'msg_created':el.msg_created,
						'msg_created_text':this.getTimeDiffFromNow(el.msg_created),
						'seen_at': el.seen_at
					});

				});

				this.messages = this.messages.sort(function(a, b) {
				    a = new Date(a.msg_created);
				    b = new Date(b.msg_created);
				    return a>b ? 1 : a<b ? -1 : 0;
				});

				this.messagesLoaded = true;

				this.$nextTick(() => {
					var container = this.$refs.chat;
					container.scrollTop = container.scrollHeight + 1000;
				});
			}).catch((e)=>{
				console.log(e);
			});
			this.updateMsgCreatedTime();
		},
		addMessage(message){
			let current_datetime = new Date()
			let formatted_date = current_datetime.getFullYear() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getDate() + " " + current_datetime.getHours() + ":" + current_datetime.getMinutes() + ":" + current_datetime.getSeconds()

			this.writeMessage(message,formatted_date);

			axios.post('/saveMessage',{
				'receiver_id':this.friendId,
				'message':message,
			}).then((response)=>{
			})
		},
		getTimeDiffFromNow(time){
			let now = new Date();
			let then = new Date(time);
			then.setHours(then.getHours()+2);
			let diffrence = (now.getTime()-then.getTime())/1000;
			if (diffrence < 60)	return 'Przed chwilą';
			if (diffrence < 120)	return '1 minutę temu';

			for(let i=1;i<58;i++){
				if(i <= 3 || ((i % 10) <=3 && (i % 10) > 0 && i>20)){
					if (diffrence < 120 + parseInt(i*60))	return String(i+1)+' minuty temu';
				}else{
					if (diffrence < 120 + parseInt(i*60))	return String(i+1)+' minut temu';
				}
			}
			if(diffrence < 604800)
			{
				// console.log(now.getDate(),then.getDate());
				if(now.getDate() == then.getDate())
				{
					return 'Dzisiaj, '+("0"+then.getHours()).slice(-2)+':'+("0"+then.getMinutes()).slice(-2);
				}
				let yesterday = new Date();
				yesterday.setDate(yesterday.getDate()-1);
				if(then.getDate() == yesterday.getDate() && yesterday.getMonth())
				{
					return 'Wczoraj, '+("0"+then.getHours()).slice(-2)+':'+("0"+then.getMinutes()).slice(-2);
				}
				let day_before_yesterday = new Date();
				day_before_yesterday.setDate(day_before_yesterday.getDate()-2);
				if(then.getDate() == yesterday.getDate())
				{
					return 'Przedwczoraj, '+("0"+then.getHours()).slice(-2)+':'+("0"+then.getMinutes()).slice(-2);
				}else{
					let day_of_week = ''
					if(then.getDay() == 0) day_of_week = 'Niedziela';
					if(then.getDay() == 1) day_of_week = 'Poniedziałek';
					if(then.getDay() == 2) day_of_week = 'Wtorek';
					if(then.getDay() == 3) day_of_week = 'Środa';
					if(then.getDay() == 4) day_of_week = 'Czwartek';
					if(then.getDay() == 5) day_of_week = 'Piątek';
					if(then.getDay() == 6) day_of_week = 'Sobota';
					return day_of_week+" "+("0"+then.getHours()).slice(-2)+':'+("0"+then.getMinutes()).slice(-2);
				}
			}
				let formatted_date = then.getFullYear() + "-" + ("0"+String(then.getMonth()+1)).slice(-2) + "-" + ("0"+then.getDate()).slice(-2) + " " +("0"+then.getHours()).slice(-2)+':'+("0"+then.getMinutes()).slice(-2);
				return formatted_date
		},
		isThisLastMessageInThisBlock(index){
			if(index<this.messages.length-1)
			{
				if(parseInt(this.messages[index].author_id) === parseInt(this.messages[index+1].author_id)) return false;
			}
			return true
		},
		writeMessage(message,msg_created,id=this.userData.id){
			this.messages.push({'author_id':id,'text':message,'msg_created':msg_created,'msg_created_text':this.getTimeDiffFromNow(msg_created)});
			this.$nextTick(() => {
				var container = this.$refs.chat;
				container.scrollTop = container.scrollHeight + 1000;
		  });

			if(id!=this.userData.id){
				console.log('sn');
				this.seenLastMessage(id);
			}

		},
		seenLastMessage(id){
			axios.post('/seen-last-msg',{friend_id:id}).then((response)=>{
				console.log(response);
			}).catch((e)=>{
				console.log(e)
			});
		},
		updateMsgCreatedTime(){
			for(var i=0;i<this.messages.length;i++)
			{
				this.messages[i].msg_created_text = this.getTimeDiffFromNow(this.messages[i].msg_created);
			}
			setInterval(this.updateMsgCreatedTime,60000);
		},
		isThisMessageYours(author_id){
			return author_id == this.userData.id;
		},
		seenLastMsg(){
			let current_datetime = new Date()
			let formatted_date = current_datetime.getFullYear() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getDate() + " " + current_datetime.getHours() + ":" + current_datetime.getMinutes() + ":" + current_datetime.getSeconds();
			// this.messages[this.messages.length - 1].seen_at = true;
			Vue.set(this.messages[this.messages.length - 1],'seen_at',formatted_date);
		},

	}
}
</script>
