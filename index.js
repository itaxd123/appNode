
const express = require('express');
const app = express();
const axios = require('axios');
const path = require('path');
const port = 3000;

// Configurar middleware para procesar datos de formulario
//app.use(express.static('public'));
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.urlencoded({ extended: true }));

app.get('/', (req, res) => {
  // Envía el archivo HTML del formulario como respuesta
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.post('/submit', (req, res) => {
    const name = req.body.name;
    const email = req.body.email;
    const englishLevel = req.body['english-level'];

    // crear el usuario y lo pone en minuscula

    const postData = {
        name: name,
        email: email,
        english_level: englishLevel 
      };

    const url = 'https://test.italogchumbile.com/wp-json/wp/v2/users/register';

      axios.post(url, postData)
      .then(response => {
        console.log(response.status);
      })
      .catch(error => {
        console.error(error);
      });

    res.send('¡Formulario enviado correctamente!');
  });


// function getUsername(data){
//     return data.substring(0, data.indexOf('@')).toLowerCase();
// }


app.listen(port, () => console.log(`Servidor escuchando en el puerto ${port}`));